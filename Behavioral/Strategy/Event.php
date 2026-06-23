<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.06.2026 - 22:00
 */

namespace Behavioral\Strategy;

use DateTimeImmutable;
use InvalidArgumentException;
use const Override;

/**
 * Enum для приоритета (PHP 8.1+)
 * Типизированный перечень для контроля значений.
 */
enum QueuePriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
}

/**
 * Class Event
 * DTO для события.
 * Демонстрирует PHP 8.4 Property Hooks для валидации без лишних сеттеров и магических методов.
 *
 * @package Behavioral\Strategy
 */
class Event
{
    // PHP 8.4 Property Hook: валидация и тримминг прямо при записи свойства
    public string $payload {
        set {
            if (empty(trim($value))) {
                throw new \InvalidArgumentException('Event payload cannot be empty');
            }
            $this->payload = trim($value);
        }
    }

    public function __construct(
        string $payload,
        // PHP 8.1:  Инициализация + только чтение,  свойства
        public readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
        public readonly QueuePriority $priority = QueuePriority::NORMAL,
    ) {
        $this->payload = $payload; // Автоматически вызывает set-хук
    }
}

/**
 * Interface MessageHandlerStrategy
 * Базовый интерфейс стратегии. Определяет контракт для обработки событий.
 *
 * @package Behavioral\Strategy
 */
interface MessageHandlerStrategy
{
    public function handle(Event $event): void;
}

/**
 * Class FileLogStrategy
 * Конкретная стратегия 1: Синхронное логгирование в файл/поток.
 *
 * @package Behavioral\Strategy
 */
class FileLogStrategy implements MessageHandlerStrategy
{
    public function __construct(
        // PHP 8.4 Asymmetric Visibility: Идеально для конфигов!
        // читать можно извне (public), но менять только внутри класса (private(set)).
        public private(set) string $filePath = 'php://stdout'
    ) {
    }


    #[\Override] // PHP 8.3+: Гарантия, переопределения метода интерфейса
    public function handle(Event $event): void
    {
        $formatted = sprintf(
            "[%s] [%s] %s\n",
            $event->timestamp->format('H:i:s'),
            strtoupper($event->priority->value),
            $event->payload
        );
        file_put_contents($this->filePath, $formatted, FILE_APPEND);
    }
}

/**
 * Class QueueDispatchStrategy
 * Конкретная стратегия 2: Асинхронная постановка в очередь (RabbitMQ, Redis, SQS).
 *
 * @package Behavioral\Strategy
 */
class QueueDispatchStrategy implements MessageHandlerStrategy
{
    public function __construct(
        public private(set) string $queueName = 'events_queue',
        public private(set) string $connection = 'redis://127.0.0.1:6379'
    ) {
    }

    #[\Override]
    public function handle(Event $event): void
    {
        // Имитация сериализации и отправки в брокер сообщений
        $job = json_encode([
            'time' => $event->timestamp->format('c'),
            'data' => $event->payload,
            'priority' => $event->priority->value,
        ]);

        echo "🚀 Pushed to queue [{$this->queueName}] via {$this->connection}: {$job}\n";
    }
}

/**
 * Class EventDispatcher
 * Context (Контекст). Класс, который использует стратегию, не зная её внутренней кухни.
 *
 * @package Behavioral\Strategy
 */
class EventDispatcher
{
    /**
     * EventDispatcher constructor.
     * Внедряем стратегию через конструктор (Constructor Property Promotion).
     * Никакого лишнего boilerplate кода!
     *
     * @param MessageHandlerStrategy $strategy
     */
    public function __construct(
        private readonly MessageHandlerStrategy $strategy
    ) {
    }

    public function dispatch(Event $event): void
    {
        // Делегируем выполнение конкретной стратегии
        $this->strategy->handle($event);
    }
}

/**
 * Как это работает: @TODO
 *
 * Преимущества:
 *    Open/Closed Principle (SOLID): Вы можете добавить новую стратегию (например, SlackNotificationStrategy или DatabaseAuditStrategy), вообще не трогая код EventDispatcher.
 *    Single Responsibility: EventDispatcher только маршрутизирует события, а тяжелая логика записи в файл или работы с сетью изолирована в отдельных классах.
 *    Избавление от (boilerplate)"лапши": Больше не нужно писать гигантские match или if/else(hell) конструкции внутри контекста (if ($type === 'log') ... elseif ($type === 'queue') ...).
 *    Тестируемость: В Unit-тестах вы легко можете подменить реальную стратегию на MockStrategy, которая просто сохранит вызовы в массив для проверок (asserts).
 *    Минимум Boilerplate: Благодаря PHP 8.4 (Property Hooks, Asymmetric Visibility) и Constructor Promotion код читается как документация.
 * Недостатки:
 *    Усложнение архитектуры: Создание отдельных классов для тривиальных задач может показаться избыточным (оверинжиниринг), если логика состоит из двух строк кода.
 *    Осведомленность клиента: Клиентский код (или DI-контейнер) должен знать о существовании разных стратегий, чтобы правильно сконфигурировать контекст.
 *
 * Где используется в популярных фреймворках (Real-world examples)(no-code)
 *
 * 1. Monolog (Logging)
 *    Контекст: Logger
 *    Стратегии (Handlers): StreamHandler (файл), SlackWebhookHandler, ElasticSearchHandler, SyslogHandler.
 *    Суть: Логгер не знает, куда писать. Он просто передает запись массиву хэндлеров (стратегий), каждый из которых решает, как её обработать.
 * 2. Laravel Notifications / Symfony Notifier
 *    Контекст: NotificationManager
 *    Стратегии (Channels): MailChannel, SmsChannel, SlackChannel, DatabaseChannel.
 *    Суть: Вы описываете уведомление один раз, а фреймворк применяет стратегию доставки в зависимости от настроек пользователя или типа события.
 * 3. Guzzle / HTTP Clients (Middleware & Auth)
 *    Контекст: HTTP Client
 *    Стратегии: BasicAuth, BearerToken, OAuth2 (Аутентификация). ExponentialBackoff, LinearDelay (Retry-стратегии при ошибках сети).
 * 4. Symfony Cache / Laravel Cache
 *    Контекст: CacheItemPoolInterface / Repository
 *    Стратегии (Drivers): RedisStore, FileStore, MemcachedStore, ArrayStore.
 *    Суть: Приложение просто вызывает $cache->get('key'), а под капотом работает стратегия, специфичная для выбранного хранилища.
 * 5. Doctrine DBAL
 *    Контекст: Connection
 *    Стратегии (Platforms): MySQLPlatform, PostgreSQLPlatform, SQLitePlatform.
 *    Суть: Генерация специфичного SQL-диалекта (например, пагинация или работа с JSON-полями) делегируется платформе конкретной БД.
 *
 * Когда полезен в реальных проектах:
 *    Обработка платежей: Стратегии StripePaymentStrategy, PayPalStrategy, CryptoStrategy.
 *    Экспорт данных: Стратегии CsvExporter, ExcelExporter, PdfExporter.
 *    Геолокация и карты: Стратегии GoogleMapsStrategy, OpenStreetMapStrategy (позволяет переключать провайдеров, если у одного закончились лимиты API).
 *    Микросервисная архитектура: Абстрагирование транспорта. Сегодня вы общаетесь с другим сервисом через HttpStrategy, а завтра переключаете на RabbitMqStrategy или GrpcStrategy, не меняя бизнес-логику.
 */

// === Клиентский код ===

// 1. Сценарий: Простое логгирование (например, для отладки или аудита)
$logger = new EventDispatcher(new FileLogStrategy());
$logger->dispatch(
    new Event('User #123 logged in', priority: QueuePriority::LOW)
);
$logger->dispatch(
    new Event('Payment failed', priority: QueuePriority::HIGH)
);

// 2. Сценарий: Постановка в очередь (для тяжелых задач, не блокирующих HTTP response)
$queueDispatcher = new EventDispatcher(
    new QueueDispatchStrategy(queueName: 'emails_queue', connection: 'amqp://guest:guest@rabbitmq')
);
$queueDispatcher->dispatch(
    new Event('Send welcome email to user@test.com')
);
$queueDispatcher->dispatch(
    new Event('Generate monthly PDF report', priority: QueuePriority::LOW)
);

// 3. Демонстрация работы PHP 8.4 Property Hooks (Валидация)
try {
    $badEvent = new Event('   '); // Пустая строка после trim()
} catch (InvalidArgumentException $e) {
    echo 'Caught Exception: ' . $e->getMessage() . "\n";
}

<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.06.2026 - 19:58
 */

namespace Behavioral\Strategy;

/**
 * Interface DataHandlerStrategy
 * Интерфейс стратегии, определяющий общий контракт для всех вариантов обработки.
 *
 * @package Behavioral\Strategy
 */
interface DataHandlerStrategy
{
    /**
     * @param string $data Данные для обработки
     * @param array $context Дополнительный контекст (например, теги, метаданные)
     * @return bool Результат выполнения операции
     */
    public function handle(string $data, array $context = []): bool;
}

/**
 * Class LogHandler
 * Конкретная стратегия: запись данных в лог-файл.
 *
 * @package Behavioral\Strategy
 */
class LogHandler implements DataHandlerStrategy
{
    // В PHP 8.4 используем promoted properties. Никаких лишних объявлений свойств(boilerplate)!
    public function __construct(
        private readonly string $logFile = 'app.log'
    ) {
    }

    /**
     * handle
     * @param string $data
     * @param array $context
     * @return bool
     */
    public function handle(string $data, array $context = []): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] LOG: {$data}" . PHP_EOL;

        // Используем блокировку файла для безопасной записи в многопоточной среде
        if (file_put_contents($this->logFile, $message, FILE_APPEND | LOCK_EX)) {
            echo "Данные записаны в лог: {$data}\n";
            return true;
        }

        return false;
    }
}

/**
 * Class QueueHandler
 * Конкретная стратегия: постановка данных в очередь (имитация Redis/RabbitMQ).
 *
 * @package Behavioral\Strategy
 */
class QueueHandler implements DataHandlerStrategy
{
    public function __construct(
        private readonly string $queueName = 'default'
    ) {
    }

    /**
     * handle
     * @param string $data
     * @param array $context
     * @return bool
     */
    public function handle(string $data, array $context = []): bool
    {
        // В реальном проекте здесь был бы вызов Predis или AmqpLib:
        // $this->redis->lpush($this->queueName, json_encode([...]));

        echo "Данные поставлены в очередь '{$this->queueName}': {$data}\n";

        return true;
    }
}

/**
 * Class Processor
 * Контекст (Context). Этот класс использует стратегию.
 * Он не знает, КАК именно обрабатываются данные, он просто делегирует это стратегии.
 *
 * @package Behavioral\Strategy
 */
class Processor
{
    // Стратегия внедряется через конструктор (Dependency Injection)
    public function __construct(
        private DataHandlerStrategy $handler
    ) {
    }

    /**
     * Позволяет изменить стратегию на лету (опционально, но часто используется в Strategy)
     */
    public function setHandler(DataHandlerStrategy $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * process
     * @param string $payload
     * Выполнение обработки
     */
    public function process(string $payload): void
    {
        // Контекст может передавать доп. метаданные стратегиям
        $metadata = ['time' => time()];

        $this->handler->handle($payload, $metadata);
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    DataHandlerStrategy - общий интерфейс для всех алгоритмов обработки.
 *    LogHandler и QueueHandler - конкретные стратегии, инкапсулирующие алгоритмы (лог, очередь).
 *    Processor - контекст, который делегирует работу стратегии.
 *
 * Преимущества:
 *    Изоляция кода - алгоритмы логирования и очередей не размазаны по коду, а изолированы.
 *    Замена на лету - можно легко переключаться между записью в лог и отправкой в очередь.
 *    Открытость/Закрытость (OCP) - легко добавить новую стратегию (например, SendToSentryHandler),
 *    не меняя код класса Processor.
 * Недостатки:
 *    Увеличение количества классов - для каждой новой вариации поведения нужен новый класс.
 *
 * Где используется (в ключевых фреймворках):
 * 1. Laravel - Cache Repositories
 *    Cache::store('redis')->put('key', 'value');
 *    Cache::store('file')->put('key', 'value');
 *    // Внутри Laravel переключает стратегии кэширования (FileStore, RedisStore, MemcachedStore).
 * 2. Symfony - Output Format (Serializer / VarDumper)
 *    $serializer->serialize($data, 'json');
 *    $serializer->serialize($data, 'xml');
 *    // Используются стратегии кодирования (JsonEncoder, XmlEncoder).
 * 3. Laravel - Filesystems (Flysystem)
 *    Storage::disk('s3')->put('file.txt', 'Contents');
 *    Storage::disk('local')->put('file.txt', 'Contents');
 *    // Разные "диски" — это стратегии работы с файловым хранилищем (Local, S3, FTP).
 * 4. Symfony - Password Hashing
 *    $hasher = new NativePasswordHasher();
 *    // Используются разные стратегии хэширования паролей (Bcrypt, Argon2i, Pbkdf2) - 4й параметр string $algorithm .
 *
 * Когда полезен:
 *    Когда есть несколько вариантов выполнения одного и того же действия (сохранить в БД / отправить в API).
 *    Избежание громоздких условных операторов (if/else или switch) в бизнес-логике.
 *    Когда нужно скрывать детали реализации сложных алгоритмов от клиента.
 */

// --- Пример использования ---
$payload = json_encode(['event' => 'user_registered', 'user_id' => 42]);

// 1. Использование через внедрение конструктора
$loggerProcessor = new Processor(new LogHandler('events.log'));
$loggerProcessor->process($payload);
// Output: Данные записаны в лог: {"event":"user_registered","user_id":42}

// 2. Изменение стратегии на лету через сеттер (если требуется)
$loggerProcessor->setHandler(new QueueHandler('high_priority'));
$loggerProcessor->process($payload);
// Output: Данные поставлены в очередь 'high_priority': {"event":"user_registered","user_id":42}

// 3. Прямое использование конкретной стратегии без контекста (тоже валидно для простых скриптов)
$queue = new QueueHandler('notifications');
$queue->handle('Ваш код подтверждения: 1234');
// Output: Данные поставлены в очередь 'notifications': Ваш код подтверждения: 1234

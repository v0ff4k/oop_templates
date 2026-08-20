<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 17.08.2026 - 20:13
 * at: 20.08.2026 - 23:43 - optimize and (static -> simple) Factory
 */

namespace Structural\SimpleFactory;

use \PDO;

/**
 * Product Interface - общий интерфейс для всех продуктов
 */
interface Logger
{
    public function log(string $message): void;
}

/**
 * Concrete Product 1 - File Logger
 */
class FileLogger implements Logger
{
    private string $filePath;

    public function __construct(string $filePath = '/tmp/app.log')
    {
        $this->filePath = $filePath;
    }

    public function log(string $message): void
    {
        file_put_contents($this->filePath, date('Y-m-d H:i:s') . " - {$message}\n", FILE_APPEND);
    }
}

/**
 * Concrete Product 2 - Database Logger
 */
class DatabaseLogger implements Logger
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function log(string $message): void
    {
        $stmt = $this->connection->prepare('INSERT INTO logs (message, created_at) VALUES (?, ?)');
        $stmt->execute([$message, date('Y-m-d H:i:s')]);
    }
}

/**
 * Concrete Product 3 - Stdout Logger
 */
class StdoutLogger implements Logger
{
    public function log(string $message): void
    {
        echo date('Y-m-d H:i:s') . " - {$message}\n";
    }
}

/**
 * SimpleFactory - создает объекты по типу
 */
class LoggerFactory
{
    public const TYPE_FILE = 'file';
    public const TYPE_DATABASE = 'database';
    public const TYPE_STDOUT = 'stdout';

    /**
     * НЕ статический метод для создания логгеров
     */
    public function create(string $type, array $config = []): Logger
    {
        return match (strtolower($type)) {
            self::TYPE_FILE => new FileLogger($config['path'] ?? sys_get_temp_dir() . '/app.log'),
            self::TYPE_DATABASE => new DatabaseLogger($config['connection'] ?? new PDO('sqlite::memory:')),
            self::TYPE_STDOUT => new StdoutLogger(),
            default => throw new \InvalidArgumentException("Unknown logger type: $type"),
        };
    }
}


/**
 * Клиентский код
 *
 * Основная идея
 *    Product — интерфейс или базовый класс для создаваемых объектов
 *    ConcreteProduct — конкретные реализации Product
 *    SimpleFactory — класс с НЕстатическим методом create(), который возвращает экземпляры различных классов
 *
 * Как это работает:
 *    Product определяет интерфейс для создаваемых объектов
 *    ConcreteProduct реализует Product
 *    SimpleFactory содержит НЕстатический метод create(), который:
 *    Принимает тип объекта для создания
 *    Возвращает экземпляр соответствующего ConcreteProduct
 *    Инкапсулирует логику создания
 *
 * Преимущества:
 *    Простота — один класс для создания всех типов объектов
 *    Инкапсуляция — логика создания скрыта от клиента
 *    Централизация — все создание объектов в одном месте
 *    Читаемость — код клиента становится более понятным
 *    Управление зависимостями — фабрика может управлять зависимостями
 * Недостатки:
 *    Нарушение Single Responsibility — фабрика управляет многими типами
 *    Большой класс — может стать громоздким при большом количестве типов
 *    Жесткая привязка — фабрика знает о всех конкретных классах
 *    Отсутствие гибкости — сложно настраивать создание каждого типа
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Model Factories
 *    // SimpleFactory для создания моделей
 *    $factory->define(User::class, function (Faker $faker) {
 *        return [
 *            'name' => $faker->name,
 *            'email' => $faker->unique()->safeEmail,
 *            'password' => '$2y$10$TKh8H1.***.***', // secret
 *        ];
 *    });
 *    // Использование
 *    $users = factory(User::class, 50)->create();
 * 2. Laravel's Notification Channels
 *    // SimpleFactory для каналов уведомлений
 *    $notifiable->notify(new OrderShipped($order));
 *    // Внутри NotificationManager::send()
 *    $channels = $notification->via($notifiable);
 *    foreach ($channels as $channel) {
 *        $this->channel($channel)->send($notifiable, $notification);
 *    }
 *    // channel() - это SimpleFactory метод
 *    protected function channel($channel) {
 *        return match($channel) {
 *            'mail' => new MailChannel,
 *            'database' => new DatabaseChannel,
 *            'broadcast' => new BroadcastChannel,
 *        };
 *    }
 * 3. Symfony's Response Factory
 *    // SimpleFactory для HTTP ответов
 *    $response = Response::create('Hello World', 200, ['Content-Type' => 'text/html']);
 *    // Возвращает Symfony\Component\HttpFoundation\Response
 *    $jsonResponse = JsonResponse::create(['status' => 'success']);
 *    // Возвращает Symfony\Component\HttpFoundation\JsonResponse
 * 4. Yii Framework's Component Factory
 *    // SimpleFactory для компонентов
 *    $component = Yii::createObject([
 *        'class' => 'app\components\Foo',
 *        'property1' => 'value1',
 *    ]);
 *    // Или с конфигурацией
 *    $component = Yii::createObject('app\components\Foo', [$config]);
 * 5. Laravel's Cache Manager
 *    // SimpleFactory для кэша
 *    $fileCache = Cache::store('file');
 *    // Возвращает Illuminate\Cache\FileStore
 *    $redisCache = Cache::store('redis');
 *    // Возвращает Illuminate\Cache\RedisStore
 * 6. Laravel's Session Manager
 *    // SimpleFactory для сессий
 *    $request = Request::capture();
 *    $session = $request->session();
 *    // Возвращает Illuminate\Session\Store
 * 7. Laravel's Validator Factory
 *    // SimpleFactory для валидаторов
 *    $validator = Validator::make($data, [
 *        'email' => 'required|email',
 *        'password' => 'required|min:8',
 *    ]);
 *    // Возвращает Illuminate\Validation\Validator
 * 8. Laravel's View Factory
 *    // SimpleFactory для представлений
 *    $view = View::make('emails.welcome', ['user' => $user]);
 *    // Возвращает Illuminate\View\View
 * 9. Laravel's Mail Factory
 *    // SimpleFactory для отправки писем
 *    Mail::to($user)->send(new OrderShipped($order));
 *    // Внутри Mailer::send()
 *    $this->buildMessage($mailable)->to($user);
 * 10. Laravel's Event Factory
 *    // SimpleFactory для событий
 *    event(new OrderShipped($order));
 *    // Внутри Dispatcher::dispatch()
 *    $this->listener($event)->handle($event);
 * 11. Laravel's Model Factory
 *    $factory->define(User::class, function (Faker $faker) {
 *        return [ 'name' => $faker->name, 'email' => $faker->unique()->safeEmail,];
 *    });
 *    $users = factory(User::class, 50)->create();
 *
 *
 * Когда полезен:
 *    Когда нужно создать один из нескольких типов — но не нужно расширять фабрику
 *    Когда логика создания сложная — но не требует наследования
 *    Когда клиент не должен знать о конкретных классах — только о интерфейсах
 *    Когда нужно централизованное управление — все создание в одном месте
 *
 *
 *             Разница между SimpleFactory и другими паттернами:
 * StaticFactory = Один класс для всех типов
 *   Статический метод create()
 *     Простая реализация
 *       Нет наследования
 * Factory Method = Иерархия классов
 *   Виртуальный метод create()
 *     Гибкая реализация
 *       Наследование
 * Abstract Factory = Иерархия фабрик
 *   Семейство create() методов
 *     Гибкая реализация
 *       Наследование
 * Builder = Построитель объектов
 *   Шаги построения
 *     Детализированная реализация
 *       Делегирование
 *
 */

try {
    echo "=== Simple Factory Pattern Example ===\n\n";
    // Клиент не знает о конкретных классах логгеров
    $loggerFactory = new LoggerFactory();

    /** @var FileLogger $fileLogger  Пример 1: FileLogger */
    $fileLogger = $loggerFactory->create('file', ['path' => '/var/log/app.log']);
    $fileLogger->log('Application started');
    unset($fileLogger);

    try {
        /** @var DatabaseLogger $dbLogger Пример 2: DatabaseLogger */
        $dbLogger = $loggerFactory->create('database', [
            'connection' => new PDO('mysql:host=localhost;dbname=test', 'root', '')
        ]);
    } catch (\PDOException $e) {
        // Обработка ошибки подключения
    }
    $dbLogger->log('User logged in');
    unset($dbLogger);


    /** @var StdoutLogger $stdoutLogger  Пример 3: StdoutLogger */
    $stdoutLogger = $loggerFactory->create('stdout');
    $stdoutLogger->log('Debug message');
    unset($stdoutLogger);


} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

/* php8.4 *********************************

enum LoggerType: string
{
    case FILE = 'file';
    case DATABASE = 'database';
    case STDOUT = 'stdout';
}

class LoggerFactory
{
    public function create(LoggerType $type, array $config = []): Logger
    {
        return match ($type) {
            LoggerType::FILE => new FileLogger($config['path'] ?? sys_get_temp_dir() . '/app.log'),
            LoggerType::DATABASE => new DatabaseLogger(
                $config['connection'] ?? throw new \InvalidArgumentException('Connection required')
            ),
            LoggerType::STDOUT => new StdoutLogger(),
        };
    }
}

// Использование:
$factory = new LoggerFactory();
$logger = $factory->create(LoggerType::FILE, ['path' => '/var/log/app.log']);


********************************* */

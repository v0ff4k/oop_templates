<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 17.08.2026 - 20:13
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
    /**
     * Статический метод для создания логгеров
     */
    public static function create(string $type, array $config = []): Logger
    {
        return match (strtolower($type)) {
            'file' => new FileLogger($config['path'] ?? '/tmp/app.log'),
            'database' => new DatabaseLogger($config['connection'] ?? new PDO('sqlite::memory:')),
            'stdout' => new StdoutLogger(),
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
 *    SimpleFactory — класс с статическим методом create(), который возвращает экземпляры различных классов
 *
 * Как это работает:
 *    Product определяет интерфейс для создаваемых объектов
 *    ConcreteProduct реализует Product
 *    SimpleFactory содержит статический метод create(), который:
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
 *    Сложность тестирования — статические методы труднее мокировать
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
 *
 * Когда полезен:
 *    Когда нужно создать один из нескольких типов — но не нужно расширять фабрику
 *    Когда логика создания сложная — но не требует наследования
 *    Когда клиент не должен знать о конкретных классах — только о интерфейсах
 *    Когда нужно централизованное управление — все создание в одном месте
 *
 *
 *             Разница между SimpleFactory и другими паттернами:
 * SimpleFactory = Один класс для всех типов
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

    /** @var FileLogger $fileLogger  Пример 1: FileLogger */
    $fileLogger = LoggerFactory::create('file', ['path' => '/var/log/app.log']);
    $fileLogger->log('Application started');
    unset($fileLogger);


    /** @var DatabaseLogger $dbLogger Пример 2: DatabaseLogger */
    $dbLogger = LoggerFactory::create('database', [
        'connection' => new PDO('mysql:host=localhost;dbname=test', 'root', '')
    ]);
    $dbLogger->log('User logged in');
    unset($dbLogger);


    /** @var StdoutLogger $stdoutLogger  Пример 3: StdoutLogger */
    $stdoutLogger = LoggerFactory::create('stdout');
    $stdoutLogger->log('Debug message');
    unset($stdoutLogger);


} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

/* php8.4 *********************************

// Использование атрибутов для автоматической генерации SimpleFactory
#[SimpleFactory]
class PaymentMethodFactory
{
    // Фабрика будет автоматически сгенерирована
}

// Генерация SimpleFactory через атрибуты
class SimpleFactoryBuilder
{
    public function build(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstance();

        // Автоматическое создание метода create
        if (!$reflector->hasMethod('create')) {
            $method = new ReflectionMethod($class, '__construct');
            $parameters = $method->getParameters();

            $factory = new class($class, $parameters) {
                private string $class;
                private array $parameters;

                public function __construct(string $class, array $parameters)
                {
                    $this->class = $class;
                    $this->parameters = $parameters;
                }

                public static function create(string $type, array $config = []): object
                {
                    return match (strtolower($type)) {
                        // Автоматически генерируемые типы
                        default => throw new \InvalidArgumentException("Unknown type: $type"),
                    };
                }
            };

            return $factory;
        }

        return $instance;
    }
}

// Pattern matching для автоматического создания SimpleFactory
public function getFactory(string $type): object
{
    return match ($type) {
        'payment' => new class {
            public static function create(string $method): PaymentMethod
            {
                return match (strtolower($method)) {
                    'credit_card', 'card', 'cc' => new CreditCardPayment(),
                    'paypal', 'pp' => new PayPalPayment(),
                    'bank_transfer', 'transfer', 'wire' => new BankTransferPayment(),
                    'crypto', 'bitcoin', 'ethereum' => new CryptoPayment(),
                    default => throw new \InvalidArgumentException("Unknown payment method"),
                };
            }
        },
        'logger' => new class {
            public static function create(string $type, array $config = []): Logger
            {
                return match (strtolower($type)) {
                    'file' => new FileLogger($config['path'] ?? '/tmp/app.log'),
                    'database' => new DatabaseLogger($config['connection'] ?? new PDO('sqlite::memory:')),
                    'stdout' => new StdoutLogger(),
                    default => throw new \InvalidArgumentException("Unknown logger type"),
                };
            }
        },
        'notifier' => new class {
            public static function create(string $channel): Notifier
            {
                return match (strtolower($channel)) {
                    'email', 'e-mail' => new EmailNotifier(),
                    'sms', 'text' => new SmsNotifier(),
                    'push', 'notification' => new PushNotifier(),
                    default => throw new \InvalidArgumentException("Unknown notification channel"),
                };
            }
        },
        'validator' => new class {
            public static function create(string $rule, array $config = []): Validator
            {
                return match (strtolower($rule)) {
                    'email' => new EmailValidator(),
                    'required' => new RequiredValidator(),
                    'min_length', 'min' => new MinLengthValidator($config['length'] ?? 6),
                    default => throw new \InvalidArgumentException("Unknown validation rule"),
                };
            }
        },
        'renderer' => new class {
            public static function create(string $engine, mixed $config = null): Renderer
            {
                return match (strtolower($engine)) {
                    'php' => new PhpRenderer(),
                    'twig' => new TwigRenderer($config ?? new Twig\Environment(new Twig\Loader\ArrayLoader([]))),
                    'blade' => new BladeRenderer($config ?? new Illuminate\View\Factory(new Illuminate\View\FileViewFinder([]))),
                    default => throw new \InvalidArgumentException("Unknown renderer engine"),
                };
            }
        },
        default => throw new \InvalidArgumentException("Unknown factory type"),
    };
}

// Enum для типов SimpleFactory
enum FactoryType: string
{
    case PAYMENT = 'payment';
    case LOGGER = 'logger';
    case NOTIFIER = 'notifier';
    case VALIDATOR = 'validator';
    case RENDERER = 'renderer';
    case CONNECTION = 'connection';
    case DATABASE = 'database';
    case CACHE = 'cache';
    case SESSION = 'session';
    case REQUEST = 'request';
    case RESPONSE = 'response';
    case FORM = 'form';
    case VIEW = 'view';
    case MAIL = 'mail';
    case NOTIFICATION = 'notification';
}

class FactoryFactory
{
    public function create(FactoryType $type): object
    {
        return match ($type) {
            FactoryType::PAYMENT => new class {
                public static function create(string $method): PaymentMethod
                {
                    return match (strtolower($method)) {
                        'credit_card', 'card', 'cc' => new CreditCardPayment(),
                        'paypal', 'pp' => new PayPalPayment(),
                        'bank_transfer', 'transfer', 'wire' => new BankTransferPayment(),
                        'crypto', 'bitcoin', 'ethereum' => new CryptoPayment(),
                        default => throw new \InvalidArgumentException("Unknown payment method"),
                    };
                }
            },
            FactoryType::LOGGER => new class {
                public static function create(string $type, array $config = []): Logger
                {
                    return match (strtolower($type)) {
                        'file' => new FileLogger($config['path'] ?? '/tmp/app.log'),
                        'database' => new DatabaseLogger($config['connection'] ?? new PDO('sqlite::memory:')),
                        'stdout' => new StdoutLogger(),
                        default => throw new \InvalidArgumentException("Unknown logger type"),
                    };
                }
            },
            FactoryType::NOTIFIER => new class {
                public static function create(string $channel): Notifier
                {
                    return match (strtolower($channel)) {
                        'email', 'e-mail' => new EmailNotifier(),
                        'sms', 'text' => new SmsNotifier(),
                        'push', 'notification' => new PushNotifier(),
                        default => throw new \InvalidArgumentException("Unknown notification channel"),
                    };
                }
            },
            FactoryType::VALIDATOR => new class {
                public static function create(string $rule, array $config = []): Validator
                {
                    return match (strtolower($rule)) {
                        'email' => new EmailValidator(),
                        'required' => new RequiredValidator(),
                        'min_length', 'min' => new MinLengthValidator($config['length'] ?? 6),
                        default => throw new \InvalidArgumentException("Unknown validation rule"),
                    };
                }
            },
            FactoryType::RENDERER => new class {
                public static function create(string $engine, mixed $config = null): Renderer
                {
                    return match (strtolower($engine)) {
                        'php' => new PhpRenderer(),
                        'twig' => new TwigRenderer($config ?? new Twig\Environment(new Twig\Loader\ArrayLoader([]))),
                        'blade' => new BladeRenderer($config ?? new Illuminate\View\Factory(new Illuminate\View\FileViewFinder([]))),
                        default => throw new \InvalidArgumentException("Unknown renderer engine"),
                    };
                }
            },
            FactoryType::CONNECTION => new class {
                public static function create(string $driver, array $config = []): PDO
                {
                    return match (strtolower($driver)) {
                        'mysql' => new PDO(
                            "mysql:host={$config['host'] ?? 'localhost'};dbname={$config['database'] ?? 'test']}",
                            $config['username'] ?? 'root',
                            $config['password'] ?? ''
                        ),
                        'pgsql' => new PDO(
                            "pgsql:host={$config['host'] ?? 'localhost'};dbname={$config['database'] ?? 'test']}",
                            $config['username'] ?? 'root',
                            $config['password'] ?? ''
                        ),
                        'sqlite' => new PDO("sqlite:{$config['database'] ?? ':memory:'}"),
                        default => throw new \InvalidArgumentException("Unknown database driver"),
                    };
                }
            },
            FactoryType::DATABASE => new class {
                public static function connection(string $name, array $config): Connection
                {
                    // Реальная реализация из Laravel
                    return new Connection($config);
                }
            },
            FactoryType::CACHE => new class {
                public static function store(string $driver, array $config): Repository
                {
                    // Реальная реализация из Laravel
                    return new Repository(new FileStore($config));
                }
            },
            FactoryType::SESSION => new class {
                public static function driver(string $driver, array $config): Store
                {
                    // Реальная реализация из Laravel
                    return new Store($config);
                }
            },
            FactoryType::REQUEST => new class {
                public static function create(string $uri, string $method = 'GET', array $parameters = []): Request
                {
                    return Request::create($uri, $method, $parameters);
                }
            },
            FactoryType::RESPONSE => new class {
                public static function create(string $content = '', int $status = 200, array $headers = []): Response
                {
                    return new Response($content, $status, $headers);
                }
            },
            FactoryType::FORM => new class {
                public static function create(string $type, array $data = []): FormInterface
                {
                    // Реальная реализация из Symfony
                    return new Form($type, $data);
                }
            },
            FactoryType::VIEW => new class {
                public static function make(string $view, array $data = []): View
                {
                    // Реальная реализация из Laravel
                    return new View($view, $data);
                }
            },
            FactoryType::MAIL => new class {
                public static function to(string $email, string $name = ''): Mailable
                {
                    // Реальная реализация из Laravel
                    return (new Mailable())->to($email, $name);
                }
            },
            FactoryType::NOTIFICATION => new class {
                public static function send(mixed $notifiable, Notification $notification): void
                {
                    // Реальная реализация из Laravel
                    $notification->send($notifiable);
                }
            },
        };
    }
}


********************************* */

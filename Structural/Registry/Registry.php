<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 03.08.2026 - 18:26
 */

namespace Structural\Registry;

use Exception;
use InvalidArgumentException;
use \PDO;

/**
 * Registry - центральный контейнер для хранения объектов
 * Банально, но имя вот такое)
 */
class Registry
{
    private static array $registry = [];
    private static array $singletons = [];

    /**
     * Регистрация объекта
     */
    public static function set(string $key, mixed $value): void
    {
        self::$registry[$key] = $value;
    }

    /**
     * Получение объекта
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$registry[$key] ?? $default;
    }

    /**
     * Проверка существования
     */
    public static function has(string $key): bool
    {
        return isset(self::$registry[$key]);
    }

    /**
     * Удаление объекта
     */
    public static function remove(string $key): void
    {
        unset(self::$registry[$key]);
    }

    /**
     * Очистка реестра
     */
    public static function clear(): void
    {
        self::$registry = [];
    }

    /**
     * Регистрация синглтона
     */
    public static function setSingleton(string $key, callable $factory): void
    {
        self::$singletons[$key] = [
            'factory' => $factory,
            'instance' => null,
        ];
    }

    /**
     * Получение синглтона
     */
    public static function getSingleton(string $key): mixed
    {
        if (!isset(self::$singletons[$key])) {
            throw new InvalidArgumentException("Singleton $key not registered");
        }

        if (self::$singletons[$key]['instance'] === null) {
            self::$singletons[$key]['instance'] = call_user_func(self::$singletons[$key]['factory']);
        }

        return self::$singletons[$key]['instance'];
    }

    /**
     * Сброс синглтонов
     */
    public static function resetSingleton(string $key): void
    {
        if (isset(self::$singletons[$key])) {
            self::$singletons[$key]['instance'] = null;
        }
    }

    /**
     * Получение всех зарегистрированных объектов
     */
    public static function getAll(): array
    {
        return self::$registry;
    }
}

/**
 * Пример 2: Registry с типизацией
 */
class TypedRegistry
{
    private static array $registry = [];

    /**
     * Регистрация объекта с проверкой типа
     */
    public static function set(string $key, object $value): void
    {
        self::$registry[$key] = $value;
    }

    /**
     * Получение объекта с проверкой типа
     */
    public static function get(string $key): object
    {
        if (!isset(self::$registry[$key])) {
            throw new InvalidArgumentException("Object $key not found in registry");
        }

        return self::$registry[$key];
    }

    /**
     * Получение объекта определенного типа
     */
    public static function getByType(string $class): object
    {
        foreach (self::$registry as $key => $object) {
            if ($object instanceof $class) {
                return $object;
            }
        }

        throw new InvalidArgumentException("Object of type $class not found in registry");
    }

    /**
     * Проверка типа при получении
     */
    public static function getIfType(string $key, string $class): ?object
    {
        if (isset(self::$registry[$key]) && self::$registry[$key] instanceof $class) {
            return self::$registry[$key];
        }

        return null;
    }
}

/**
 * Пример 3: Registry с пространствами имен
 */
class NamespacedRegistry
{
    private static array $registry = [];

    /**
     * Регистрация в пространстве имен
     */
    public static function set(string $namespace, string $key, mixed $value): void
    {
        self::$registry[$namespace][$key] = $value;
    }

    /**
     * Получение из пространства имен
     */
    public static function get(string $namespace, string $key, mixed $default = null): mixed
    {
        return self::$registry[$namespace][$key] ?? $default;
    }

    /**
     * Проверка существования в пространстве имен
     */
    public static function has(string $namespace, string $key): bool
    {
        return isset(self::$registry[$namespace][$key]);
    }

    /**
     * Получение всех объектов из пространства имен
     */
    public static function getAllInNamespace(string $namespace): array
    {
        return self::$registry[$namespace] ?? [];
    }

    /**
     * Очистка пространства имен
     */
    public static function clearNamespace(string $namespace): void
    {
        unset(self::$registry[$namespace]);
    }
}

/**
 * Пример 4: Registry с ленивой загрузкой
 */
class LazyRegistry
{
    private static array $registry = [];
    private static array $factories = [];

    /**
     * Регистрация с ленивой загрузкой
     */
    public static function register(string $key, callable $factory): void
    {
        self::$factories[$key] = $factory;
        unset(self::$registry[$key]); // Удаляем из кэша, если был
    }

    /**
     * Получение с ленивой загрузкой
     */
    public static function get(string $key): mixed
    {
        if (!isset(self::$registry[$key])) {
            if (!isset(self::$factories[$key])) {
                throw new InvalidArgumentException("Object $key not registered");
            }
            self::$registry[$key] = call_user_func(self::$factories[$key]);
        }

        return self::$registry[$key];
    }

    /**
     * Сброс кэша для пересоздания
     */
    public static function reset(string $key): void
    {
        unset(self::$registry[$key]);
    }
}

/**
 * Пример 5: Registry с событиями
 */
class EventfulRegistry
{
    private static array $registry = [];
    private static array $listeners = [];

    /**
     * Регистрация объекта
     */
    public static function set(string $key, mixed $value): void
    {
        $oldValue = self::$registry[$key] ?? null;
        self::$registry[$key] = $value;

        self::dispatchEvent('set', $key, $value, $oldValue);
    }

    /**
     * Вызов события
     */
    private static function dispatchEvent(string $event, mixed ...$args): void
    {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $listener(...$args);
            }
        }
    }

    /**
     * Получение объекта
     */
    public static function get(string $key): mixed
    {
        return self::$registry[$key] ?? null;
    }

    /**
     * Подписка на события
     */
    public static function on(string $event, callable $listener): void
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Отписка от событий
     */
    public static function off(string $event, callable $listener): void
    {
        if (isset(self::$listeners[$event])) {
            $key = array_search($listener, self::$listeners[$event], true);
            if ($key !== false) {
                unset(self::$listeners[$event][$key]);
            }
        }
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Registry — центральный контейнер, который хранит объекты
 *    Клиент — получает доступ к объектам через Registry
 *    Объекты — регистрируются в Registry и могут быть извлечены
 *
 * Как это работает:
 *    Registry хранит объекты в статическом массиве
 *    Клиенты получают доступ к объектам через статические методы
 *    Объекты могут быть зарегистрированы один раз и использованы в любом месте приложения
 *
 * Преимущества:
 *    Глобальный доступ — любой код может получить доступ к зарегистрированным объектам
 *    Простота — легко использовать и понимать
 *    Централизация — все объекты хранятся в одном месте
 *    Ленивая загрузка — можно загружать объекты только когда они нужны
 *    Синглтоны — легко реализовать паттерн Синглтон
 *
 * Недостатки:
 *    Глобальное состояние — нарушает инкапсуляцию и делает код менее предсказуемым
 *    Скрытые зависимости — сложно понять, какие объекты использует класс
 *    Тестирование — сложно мокировать глобальное состояние
 *    Жесткая привязка — классы становятся зависимыми от Registry
 *    Конфликты имен — могут возникать конфликты ключей
 *    Проблемы с производительностью — статические методы могут быть медленнее
 *
 * Где используется в фреймворках:
 * 1. Laravel's app() Helper
 *    // Laravel app() helper - Registry для сервисов контейнера
 *    $router = app('router');
 *    $cache = app('cache');
 *    $view = app('view');
 *    // Или с автоматическим разрешением зависимостей
 *    $user = app(UserController::class);
 * 2. Laravel's Facade
 *    // Laravel Facades - статический доступ к сервисам
 *    Route::get('/', function () {
 *        return View::make('welcome');
 *    });
 *    // Или
 *    Cache::put('key', 'value', 60);
 *    $value = Cache::get('key');
 * 3. Laravel's Config
 *    // Laravel Config - Registry для конфигурации
 *    $value = config('app.timezone');
 *    config(['app.debug' => true]);
 * 4. Laravel's Session
 *    // Laravel Session - Registry для сессии
 *    Session::put('key', 'value');
 *    $value = Session::get('key');
 * 5. Laravel's Cookie
 *    // Laravel Cookie - Registry для cookies
 *    Cookie::queue('name', 'value', $minutes);
 * 6. Laravel's Request
 *    // Laravel Request - Registry для текущего запроса
 *    $name = request('name');
 *    $path = request()->path();
 * 7. Symfony's ContainerInterface
 *    // Symfony ContainerInterface - Registry для сервисов
 *    $container = $this->container;
 *    $router = $container->get('router');
 *    $request = $container->get('request_stack')->getCurrentRequest();
 * 8. Symfony's ParameterBag
 *    // Symfony ParameterBag - Registry для параметров
 *    $parameterBag = $this->container->getParameterBag();
 *    $value = $parameterBag->get('kernel.environment');
 * 9. Zend Framework's ServiceLocator
 *    // Zend ServiceLocator - Registry для сервисов
 *    $serviceLocator = $serviceManager;
 *    $service = $serviceLocator->get('MyService');
 * 10. Yii Framework's Yii::$app
 *    // Yii::$app - глобальный Registry для приложения
 *    $request = Yii::$app->request;
 *    $response = Yii::$app->response;
 *    $user = Yii::$app->user;
 * 11. CakePHP's ClassRegistry
 *    $component = ClassRegistry::init('MyComponent');
 * 12. Nette's Context
 *    // Nette Context - Registry для сервисов
 *    $service = $this->context->getByType('MyService');
 * 13. Doctrine's Registry
 *    // Doctrine Registry (в старых версиях)
 *    $entityManager = \Doctrine\ORM\Registry::getEntityManager();
 * 14. Laravel's Event Registry
 *    // Laravel Event Registry
 *    Event::listen('Illuminate\OrderShipped', function ($order) {
 *        // реализация...
 *    });
 *
 * Когда полезен:
 *    Маленькие приложения — когда нужен простой способ доступа к объектам
 *    Быстрая разработка — когда важна скорость разработки, а не архитектура
 *    Утилиты и скрипты — для простых скриптов и утилит
 *    Legacy код — для поддержки старого кода, где сложно внедрить DI
 *    Тестовый код — для простых тестов, где не нужна сложная изоляция
 *
 * Почему Registry считается антипаттерном:
 *    Нарушение инверсии зависимостей — классы зависят от глобального состояния, а не от абстракций(sol-I-d)
 *    Скрытые зависимости — сложно понять, какие объекты нужны классу, только по его сигнатуре
 *    Сложность тестирования — нужно мокировать глобальное состояние, что может влиять на другие тесты
 *    Проблемы с параллелизмом — глобальное состояние может вызывать проблемы в многопоточных средах
 *    Сложность поддержки — изменения в Registry могут влиять на множество мест в коде
 *
 * Современная альтернатива: Dependency Injection Container
 *    // Вместо Registry лучше использовать DI Container
 *    $container = new Container();
 *    // Регистрация сервисов
 *    $container->set('pdo', function () {
 *        return new PDO('sqlite::memory:');
 *    });
 *    $container->set('logger', function () {
 *        return new FileLogger('app.log');
 *    });
 *    // Внедрение зависимостей (через конструктор)
 *    class UserService {
 *        public function __construct(
 *            private PDO $pdo,
 *            private Logger $logger
 *        ) {}
 *    }
 *    // Получение сервиса с автоматическим внедрением зависимостей
 *    $userService = $container->get(UserService::class);
 *
 */

try {
    //
    echo "=== Simple Registry Pattern Example ===\n\n";

    Registry::set('config', ['debug' => true, 'version' => '1.0.0']);
    Registry::set('db', new PDO('sqlite::memory:'));
    Registry::set('logger', new class {
        public function log(string $message): void
        {
            echo "Log: $message\n";
        }
    });

    echo 'Config: ' . json_encode(Registry::get('config')) . "\n";
    echo "Registry has 'db': " . (Registry::has('db') ? 'YES' : 'NO') . "\n";

    $logger = Registry::get('logger');
    $logger->log('Registry example');


    echo "\n=== Typed Registry Example ===\n";
    TypedRegistry::set('pdo', new PDO('sqlite::memory:'));
    TypedRegistry::set('mailer', new class implements EmailSender {
        public function send(string $to, string $subject, string $message): bool
        {
            echo "Email sent to $to\n";
            return true;
        }
    });

    $pdo = TypedRegistry::get('pdo');
    $pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY)');

    $mailer = TypedRegistry::get('mailer');
    $mailer->send('test@example.com', 'Hello', 'Test message');


    echo "\n=== Namespaced Registry Example ===\n";
    NamespacedRegistry::set('database', 'connection', new PDO('sqlite::memory:'));
    NamespacedRegistry::set('database', 'config', ['host' => 'localhost']);
    NamespacedRegistry::set('cache', 'redis', new Redis());
    NamespacedRegistry::set('cache', 'ttl', 3600);

    $dbConnection = NamespacedRegistry::get('database', 'connection');
    $dbConfig = NamespacedRegistry::get('database', 'config');
    $redis = NamespacedRegistry::get('cache', 'redis');

    echo 'Database config: ' . json_encode($dbConfig) . "\n";
    echo 'Cache TTL: ' . NamespacedRegistry::get('cache', 'ttl') . "\n";


    echo "\n=== Lazy Registry Example ===\n";
    LazyRegistry::register('config', function () {
        echo "Creating config object...\n";
        return ['debug' => true, 'version' => '1.0.0'];
    });

    LazyRegistry::register('db', function () {
        echo "Creating database connection...\n";
        return new PDO('sqlite::memory:');
    });

    echo "Getting config (first call)...\n";
    $config = LazyRegistry::get('config');
    echo 'Config: ' . json_encode($config) . "\n";

    echo "Getting config (second call, cached)...\n";
    $config = LazyRegistry::get('config');

    echo "Getting db connection...\n";
    $db = LazyRegistry::get('db');
    $db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY)');


    echo "\n=== Eventful Registry Example ===\n";
    EventfulRegistry::on('set', function (string $key, mixed $value, mixed $oldValue): void {
        echo "Event: set('$key', " . json_encode($value) . ")\n";
        if ($oldValue !== null) {
            echo '  Old value: ' . json_encode($oldValue) . "\n";
        }
    });

    EventfulRegistry::set('counter', 0);
    EventfulRegistry::set('counter', 1);
    EventfulRegistry::set('counter', 2);

    echo 'Counter value: ' . EventfulRegistry::get('counter') . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

/* *****************************************

// Использование атрибутов для автоматической регистрации в Registry
#[Registry]
class Config
{
    public function __construct(
        public bool $debug = true,
        public string $version = '1.0.0'
    ) {}
}

// Генерация Registry через атрибуты
class RegistryBuilder
{
    public function build(object $target): void
    {
        $reflector = new ReflectionClass($target);

        foreach ($reflector->getProperties() as $property) {
            $attributes = $property->getAttributes(RegistryProperty::class);
            foreach ($attributes as $attribute) {
                // @var RegistryProperty $registry
                $registry = $attribute->newInstance();
                $property->setValue($target, $this->get($registry->value));
            }
        }
    }
}

// Pattern matching для автоматического создания Registry
public function getRegistry(string $type): object
{
    return match ($type) {
    'config' => new ConfigRegistry(),
        'database' => new DatabaseRegistry(),
        'cache' => new CacheRegistry(),
        'logger' => new LoggerRegistry(),
        'event' => new EventRegistry(),
        default => throw new InvalidArgumentException("Unknown registry type: $type"),
    };
}

// Enum для типов Registry
enum RegistryType: string
{
    case CONFIG = 'config';
    case DATABASE = 'database';
    case CACHE = 'cache';
    case LOGGER = 'logger';
    case EVENT = 'event';
    case SESSION = 'session';
    case AUTH = 'auth';
    case TRANSLATION = 'translation';
}

class RegistryFactory
{
    public function create(RegistryType $type): object
    {
        return match ($type) {
        RegistryType::CONFIG => new ConfigRegistry(),
            RegistryType::DATABASE => new DatabaseRegistry(),
            RegistryType::CACHE => new CacheRegistry(),
            RegistryType::LOGGER => new LoggerRegistry(),
            RegistryType::EVENT => new EventRegistry(),
            RegistryType::SESSION => new SessionRegistry(),
            RegistryType::AUTH => new AuthRegistry(),
            RegistryType::TRANSLATION => new TranslationRegistry(),
        };
    }
}

***************************************** */

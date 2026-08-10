<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 08.08.2026 - 12:02
 */

namespace Creational\Multiton;

/**
 *  Пример 1: Config Multiton - класс с несколькими именованными экземплярами
 */
class Config
{
    private static array $instances = [];
    private array $config = [];

    /**
     * Приватный конструктор для предотвращения прямого создания
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Получение экземпляра по имени
     */
    public static function getInstance(string $name, array $config = []): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($config);
        }

        return self::$instances[$name];
    }

    /**
     * Удаление экземпляра
     */
    public static function removeInstance(string $name): void
    {
        unset(self::$instances[$name]);
    }

    /**
     * Проверка существования экземпляра
     */
    public static function hasInstance(string $name): bool
    {
        return isset(self::$instances[$name]);
    }

    /**
     * Получение всех экземпляров
     */
    public static function getAllInstances(): array
    {
        return self::$instances;
    }

    /**
     * Получение конфигурации
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Установка конфигурации
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Проверка наличия ключа
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Получение всех настроек
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Очистка конфигурации
     */
    public function clear(): void
    {
        $this->config = [];
    }

    /**
     * Сброс всех экземпляров
     */
    public static function reset(): void
    {
        self::$instances = [];
    }
}

/**
 * Пример 2: Multiton для подключений к базам данных
 */
class DatabaseConnection
{
    private static array $connections = [];
    private PDO $connection;

    private function __construct(string $dsn, string $username, string $password)
    {
        $this->connection = new PDO($dsn, $username, $password);
    }

    public static function getConnection(string $name, string $dsn, string $username, string $password): self
    {
        if (!isset(self::$connections[$name])) {
            self::$connections[$name] = new self($dsn, $username, $password);
        }

        return self::$connections[$name];
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getConnectionObject(): PDO
    {
        return $this->connection;
    }

    public static function closeConnection(string $name): void
    {
        unset(self::$connections[$name]);
    }

    public static function getAllConnections(): array
    {
        return self::$connections;
    }
}

/**
 * Пример 3: Multiton для кэша
 */
class Cache
{
    private static array $instances = [];
    private array $storage = [];

    private function __construct()
    {
        // Приватный конструктор
    }

    public static function getInstance(string $name): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self();
        }

        return self::$instances[$name];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->storage[$key] = [
            'value' => $value,
            'expires_at' => time() + $ttl,
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->storage[$key])) {
            return $default;
        }

        if ($this->storage[$key]['expires_at'] < time()) {
            unset($this->storage[$key]);
            return $default;
        }

        return $this->storage[$key]['value'];
    }

    public function has(string $key): bool
    {
        return isset($this->storage[$key]) && $this->storage[$key]['expires_at'] > time();
    }

    public function delete(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function clear(): void
    {
        $this->storage = [];
    }

    public static function reset(): void
    {
        self::$instances = [];
    }
}

/**
 * Пример 4: Multiton для логгеров
 */
class Logger
{
    private static array $loggers = [];
    private string $name;
    private string $filePath;

    private function __construct(string $name, string $filePath)
    {
        $this->name = $name;
        $this->filePath = $filePath;
    }

    public static function getLogger(string $name, string $filePath = '/tmp/app.log'): self
    {
        if (!isset(self::$loggers[$name])) {
            self::$loggers[$name] = new self($name, $filePath);
        }

        return self::$loggers[$name];
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            json_encode($context)
        );

        file_put_contents($this->filePath, $logEntry, FILE_APPEND);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public static function reset(): void
    {
        self::$loggers = [];
    }
}

/**
 * Пример 5: Multiton для сессий
 */
class Session
{
    private static array $instances = [];
    private string $namespace;

    private function __construct(string $namespace)
    {
        $this->namespace = $namespace;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getSession(string $namespace = 'default'): self
    {
        if (!isset(self::$instances[$namespace])) {
            self::$instances[$namespace] = new self($namespace);
        }

        return self::$instances[$namespace];
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$this->namespace][$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$this->namespace][$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$this->namespace][$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$this->namespace][$key]);
    }

    public function clear(): void
    {
        $_SESSION[$this->namespace] = [];
    }

    public function destroy(): void
    {
        unset(self::$instances[$this->namespace]);
        $_SESSION[$this->namespace] = [];
    }

    public function getAll(): array
    {
        return $_SESSION[$this->namespace] ?? [];
    }

    public static function reset(): void
    {
        self::$instances = [];
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Multiton — класс, который хранит несколько именованных экземпляров
 *    Ключи — уникальные идентификаторы для доступа к экземплярам
 *    Экземпляры — объекты, хранящиеся в реестре по ключам
 *    Клиент — получает доступ к экземплярам через имена
 *
 * Как это работает:
 *    Multiton хранит несколько экземпляров в статическом массиве
 *    Ключи используются для доступа к конкретным экземплярам
 *    Экземпляры создаются только один раз для каждого ключа
 *    Клиент получает доступ к экземплярам через статические методы
 *
 * Преимущества:
 *    Несколько глобальных состояний — можно иметь несколько независимых экземпляров
 *    Контроль создания — контролирует создание экземпляров
 *    Экономия памяти — повторно использует существующие экземпляры
 *    Простота доступа — глобальный доступ через имена
 *    Изоляция состояний — разные экземпляры могут иметь разные состояния
 * Недостатки:
 *    Глобальное состояние — нарушает инкапсуляцию
 *    Скрытые зависимости — сложно понять, какие экземпляры используются
 *    Сложность тестирования — нужно сбрасывать состояние между тестами
 *    Проблемы с параллелизмом — глобальное состояние может вызывать проблемы в многопоточных средах
 *    Жесткая привязка — классы становятся зависимыми от Multiton
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Application Instance
 *    // Laravel использует Multiton для Application
 *    $app = new Illuminate\Foundation\Application();
 *    // Доступ к экземпляру по имени
 *    $app->instance('config', new Config());
 *    $app->instance('router', new Router());
 *    $app->instance('cache', new CacheManager());
 *    // Получение экземпляров
 *    $config = $app->make('config');
 *    $router = $app->make('router');
 *    $cache = $app->make('cache');
 *    // Или через фасады
 *    Config::get('app.name');
 *    Route::get('/', '...');
 *    Cache::put('key', 'value', 60);
 * 2. Laravel's Container Instances
 *    // Laravel Container - Multiton для сервисов
 *    $container = new Illuminate\Container\Container();
 *    // Регистрация экземпляров
 *    $container->instance('db', new DatabaseManager());
 *    $container->instance('log', new Logger());
 *    $container->instance('queue', new QueueManager());
 *    // Получение экземпляров
 *    $db = $container->make('db');
 *    $log = $container->make('log');
 *    $queue = $container->make('queue');
 *    // Или через помощники
 *    $database = app('db');
 *    $logger = app('log');
 *    $queue = app('queue');
 * 3. Symfony's Container Instances
 *    // Symfony Container - Multiton для сервисов
 *    $container = new Symfony\Component\DependencyInjection\ContainerBuilder();
 *    // Регистрация экземпляров
 *    $container->set('doctrine', new DoctrineRegistry());
 *    $container->set('mailer', new Mailer());
 *    $container->set('router', new Router());
 *    // Получение экземпляров
 *    $doctrine = $container->get('doctrine');
 *    $mailer = $container->get('mailer');
 *    $router = $container->get('router');
 *    // Или через контейнер
 *    $container->get('service_id');
 * 4. Yii Framework's Yii::$app
 *    // Yii Framework - классический пример Multiton
 *    // Yii::$app - глобальный контейнер с несколькими экземплярами
 *    // Доступ к разным компонентам
 *    $request = Yii::$app->request;
 *    $response = Yii::$app->response;
 *    $user = Yii::$app->user;
 *    $cache = Yii::$app->cache;
 *    $db = Yii::$app->db;
 *    // Регистрация компонентов
 *    Yii::$app->setComponents([
 *        'db' => [
 *            'class' => 'yii\db\Connection',
 *            'dsn' => 'mysql:host=localhost;dbname=test',
 *        ],
 *        'cache' => [
 *            'class' => 'yii\caching\FileCache',
 *        ],
 *    ]);
 *    // Или в конфигурации
 *    return [
 *        'components' => [
 *            'db' => [
 *                'dsn' => 'mysql:host=localhost;dbname=test',
 *            ],
 *            'cache' => [
 *                'class' => 'yii\caching\FileCache',
 *            ],
 *        ],
 *    ];
 * 5. CakePHP's ObjectRegistry
 *    // CakePHP ClassRegistry - Multiton для классов
 *    // Регистрация класса
 *    ClassRegistry::addObject('MyComponent', new MyComponent());
 *    // Получение экземпляра
 *    $component = ClassRegistry::getObject('MyComponent');
 *    // Или
 *    $component = ClassRegistry::init('MyComponent');
 * 6. Nette's Context
 *    // Nette Context - Multiton для сервисов
 *    $context = new Nette\DI\Container();
 *    // Регистрация сервисов
 *    $context->addService('database', new Database());
 *    $context->addService('logger', new Logger());
 *    $context->addService('cache', new Cache());
 *    // Получение сервисов
 *    $database = $context->getService('database');
 *    $logger = $context->getService('logger');
 *    $cache = $context->getService('cache');
 *    // Или через контейнер
 *    $container = $context->getByType(Database::class);
 * 7. Zend Framework's ServiceManager
 *    // Zend ServiceManager - Multiton для сервисов
 *    $serviceManager = new Zend\ServiceManager\ServiceManager();
 *    // Регистрация сервисов
 *    $serviceManager->setService('db', new DbAdapter());
 *    $serviceManager->setService('log', new Logger());
 *    $serviceManager->setService('cache', new Cache());
 *    // Получение сервисов
 *    $db = $serviceManager->get('db');
 *    $log = $serviceManager->get('log');
 *    $cache = $serviceManager->get('cache');
 *    // Или через фабрики
 *    $serviceManager->setFactory('db', function ($sm) {
 *        return new DbAdapter();
 *    });
 * 8. Doctrine's Registry
 *    // Doctrine Registry (в старых версиях) - Multiton для EntityManager
 *    $registry = new \Doctrine\ORM\Registry();
 *    // Регистрация EntityManager
 *    $registry->addEntityManager('default', $entityManager);
 *    // Получение EntityManager
 *    $em = $registry->getEntityManager('default');
 *    $em2 = $registry->getEntityManager('other');
 *    // Или
 *    $em = \Doctrine\ORM\Registry::getEntityManager('default');
 * 9. Laravel's Database Manager
 *    // Laravel Database Manager - Multiton для подключений
 *    $manager = new Illuminate\Database\DatabaseManager($app);
 *    // Регистрация подключений
 *    $manager->addConnection([
 *        'driver' => 'mysql',
 *        'host' => 'localhost',
 *        'database' => 'main',
 *    ], 'main');
 *    $manager->addConnection([
 *        'driver' => 'mysql',
 *        'host' => 'replica',
 *        'database' => 'main',
 *    ], 'replica');
 *    // Получение подключений
 *    $main = $manager->connection('main');
 *    $replica = $manager->connection('replica');
 *    // Или через DB фасад
 *    $main = DB::connection('main');
 *    $replica = DB::connection('replica');
 * 10. Laravel's Cache Manager
 *    // Laravel Cache Manager - Multiton для кэша
 *    $manager = new Illuminate\Cache\CacheManager($app);
 *    // Регистрация кэшей
 *    $manager->addRepository(new FileStore($app['files'], '/path/to/cache'));
 *    $manager->addRepository(new RedisStore($app['redis']));
 *    $manager->addRepository(new DatabaseStore($app['db']));
 *    // Получение кэшей
 *    $fileCache = $manager->store('file');
 *    $redisCache = $manager->store('redis');
 *    $databaseCache = $manager->store('database');
 *    // Или через Cache фасад
 *    $fileCache = Cache::store('file');
 *    $redisCache = Cache::store('redis');
 *
 * Когда полезен:
 *    Когда нужно несколько глобальных состояний — например, несколько конфигураций, подключений к базам данных
 *    Когда нужно изолировать состояния — разные экземпляры для разных частей приложения
 *    Когда нужно управлять ресурсами — например, разные подключения к базам данных
 *    Когда нужен глобальный доступ — но с возможностью иметь несколько экземпляров
 *
 * Разница между Singleton и Multiton:
 * Singleton = Один глобальный экземпляр
 *  Один ключ доступа
 *    Проще в реализации
 *      Один экземпляр на весь класс
 *        Глобальное состояние
 * Multiton = Несколько именованных экземпляров
 *  Несколько ключей доступа
 *    Сложнее в реализации
 *      Несколько экземпляров на один класс
 *        Несколько глобальных состояний
 *
 */
try {
    echo "=== Multiton Pattern Example ===\n\n";

    // Пример 1: Config Multiton
    echo "=== Config Multiton ===\n";

    $appConfig = Config::getInstance('app', [
        'name' => 'My Application',
        'version' => '1.0.0',
        'debug' => true,
    ]);

    $databaseConfig = Config::getInstance('database', [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'test',
        'user' => 'root',
        'password' => '',
    ]);

    $cacheConfig = Config::getInstance('cache', [
        'enabled' => true,
        'ttl' => 3600,
        'driver' => 'file',
    ]);

    echo 'App Name: ' . $appConfig->get('name') . "\n";
    echo 'Database Host: ' . $databaseConfig->get('host') . "\n";
    echo 'Cache TTL: ' . $cacheConfig->get('ttl') . "\n";
    echo "Has 'app' instance: " . (Config::hasInstance('app') ? 'YES' : 'NO') . "\n";
    echo 'All instances: ' . json_encode(array_keys(Config::getAllInstances())) . "\n\n";

    // Пример 2: DatabaseConnection Multiton
    echo "=== DatabaseConnection Multiton ===\n";

    $defaultConnection = DatabaseConnection::getConnection(
        'default',
        'mysql:host=localhost;dbname=test',
        'root',
        ''
    );

    $replicaConnection = DatabaseConnection::getConnection(
        'replica',
        'mysql:host=replica;dbname=test',
        'replica_user',
        'replica_pass'
    );

    $analyticsConnection = DatabaseConnection::getConnection(
        'analytics',
        'mysql:host=analytics;dbname=analytics',
        'analytics_user',
        'analytics_pass'
    );

    echo 'Default connection: ' . spl_object_hash($defaultConnection) . "\n";
    echo 'Replica connection: ' . spl_object_hash($replicaConnection) . "\n";
    echo 'Analytics connection: ' . spl_object_hash($analyticsConnection) . "\n";
    echo 'All connections: ' . json_encode(array_keys(DatabaseConnection::getAllConnections())) . "\n\n";

    // Пример 3: Cache Multiton
    echo "=== Cache Multiton ===\n";

    $defaultCache = Cache::getInstance('default');
    $userCache = Cache::getInstance('user');
    $productCache = Cache::getInstance('product');

    $defaultCache->set('key1', 'value1', 60);
    $userCache->set('user:123', ['name' => 'John', 'email' => 'john@example.com']);
    $productCache->set('product:456', ['name' => 'Laptop', 'price' => 999.99]);

    echo 'Default cache key1: ' . $defaultCache->get('key1') . "\n";
    echo 'User cache user:123: ' . json_encode($userCache->get('user:123')) . "\n";
    echo 'Product cache product:456: ' . json_encode($productCache->get('product:456')) . "\n";
    echo 'All cache instances: ' . json_encode(array_keys(Cache::getAllInstances())) . "\n\n";

    // Пример 4: Logger Multiton
    echo "=== Logger Multiton ===\n";

    $appLogger = Logger::getLogger('app', '/var/log/app.log');
    $securityLogger = Logger::getLogger('security', '/var/log/security.log');
    $paymentLogger = Logger::getLogger('payment', '/var/log/payment.log');

    $appLogger->info('Application started');
    $securityLogger->info('User login attempt', ['user_id' => 123]);
    $paymentLogger->error('Payment failed', ['order_id' => 456, 'reason' => 'Insufficient funds']);

    echo 'App logger file: ' . $appLogger->getFilePath() . "\n";
    echo 'Security logger file: ' . $securityLogger->getFilePath() . "\n";
    echo 'Payment logger file: ' . $paymentLogger->getFilePath() . "\n";
    echo 'All logger instances: ' . json_encode(array_keys(Logger::getAllInstances())) . "\n\n";

    // Пример 5: Session Multiton
    echo "=== Session Multiton ===\n";

    $defaultSession = Session::getSession('default');
    $adminSession = Session::getSession('admin');
    $apiSession = Session::getSession('api');

    $defaultSession->set('user_id', 123);
    $defaultSession->set('cart', ['product_id' => 456, 'quantity' => 2]);

    $adminSession->set('is_admin', true);
    $adminSession->set('permissions', ['read', 'write', 'delete']);

    $apiSession->set('api_key', 'abc123def456');
    $apiSession->set('rate_limit', 100);

    echo 'Default session user_id: ' . $defaultSession->get('user_id') . "\n";
    echo 'Admin session permissions: ' . json_encode($adminSession->get('permissions')) . "\n";
    echo 'API session rate_limit: ' . $apiSession->get('rate_limit') . "\n";
    echo 'All session instances: ' . json_encode(array_keys(Session::getAllInstances())) . "\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/* 8.4  ***********************

// Использование атрибутов для автоматической регистрации Multiton
#[Multiton]
class Config
{
    private array $config = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }
}

// Генерация Multiton через атрибуты
class MultitonBuilder
{
    public function build(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstanceWithoutConstructor();

        // Автоматическая регистрация экземпляра
        $multiton = new class($class, $instance) {
            private string $class;
            private object $instance;
            private static array $instances = [];

            public function __construct(string $class, object $instance)
            {
                $this->class = $class;
                $this->instance = $instance;
            }

            public static function getInstance(string $name, array $config = []): object
            {
                if (!isset(self::$instances[$name])) {
                    $reflector = new ReflectionClass($this->class);
                    $instance = $reflector->newInstance($config);
                    self::$instances[$name] = $instance;
                }

                return self::$instances[$name];
            }
        };

        return $multiton;
    }
}

// Pattern matching для автоматического создания Multiton
public function getInstance(string $type, string $name, array $config = []): object
{
    return match ($type) {
        'config' => Config::getInstance($name, $config),
        'database' => DatabaseConnection::getConnection($name, ...$config),
        'cache' => Cache::getInstance($name),
        'logger' => Logger::getLogger($name, $config['path'] ?? '/tmp/app.log'),
        'session' => Session::getSession($name),
        default => throw new InvalidArgumentException("Unknown multiton type"),
    };
}

// Enum для типов Multiton
enum MultitonType: string
{
    case CONFIG = 'config';
    case DATABASE = 'database';
    case CACHE = 'cache';
    case LOGGER = 'logger';
    case SESSION = 'session';
    case CONNECTION = 'connection';
    case STORE = 'store';
    case MANAGER = 'manager';
}

class MultitonFactory
{
    public function create(MultitonType $type, string $name, array $config = []): object
    {
        return match ($type) {
            MultitonType::CONFIG => Config::getInstance($name, $config),
            MultitonType::DATABASE => DatabaseConnection::getConnection(
                $name,
                $config['dsn'] ?? '',
                $config['username'] ?? '',
                $config['password'] ?? ''
            ),
            MultitonType::CACHE => Cache::getInstance($name),
            MultitonType::LOGGER => Logger::getLogger(
                $name,
                $config['path'] ?? '/tmp/app.log'
            ),
            MultitonType::SESSION => Session::getSession($name),
            MultitonType::CONNECTION => match ($config['type'] ?? 'mysql') {
                'mysql' => DatabaseConnection::getConnection($name, ...$config),
                'pgsql' => DatabaseConnection::getConnection($name, ...$config),
                'sqlite' => DatabaseConnection::getConnection($name, ...$config),
                default => throw new InvalidArgumentException("Unknown connection type"),
            },
            MultitonType::STORE => match ($config['driver'] ?? 'file') {
                'file' => Cache::getInstance($name),
                'redis' => Cache::getInstance($name),
                'memcached' => Cache::getInstance($name),
                default => throw new InvalidArgumentException("Unknown store driver"),
            },
            MultitonType::MANAGER => match ($config['manager'] ?? 'database') {
                'database' => new DatabaseManager(),
                'cache' => new CacheManager(),
                'log' => new LogManager(),
                default => throw new InvalidArgumentException("Unknown manager type"),
            },
        };
    }
}

*************************************************** */

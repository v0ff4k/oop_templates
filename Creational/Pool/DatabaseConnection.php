<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 12.08.2026 - 15:14
 */

namespace Creational\Pool;

/**
 * Пример 1: DatabaseConnection подключение к базам данных
 * Reusable - интерфейс для объектов, которые можно повторно использовать
 */
interface DatabaseConnection
{
    public function connect(): void;
    public function query(string $sql): array;
    public function disconnect(): void;
    public function isConnected(): bool;
}

/**
 * Concrete Reusable - конкретное подключение к базе данных
 */
class MySQLConnection implements DatabaseConnection
{
    private string $host;
    private string $database;
    private string $username;
    private string $password;
    private ?PDO $connection = null;
    private bool $inUse = false;

    public function __construct(string $host, string $database, string $username, string $password)
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect(): void
    {
        if ($this->connection === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->database}";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function query(string $sql): array
    {
        if ($this->connection === null) {
            $this->connect();
        }

        $stmt = $this->connection->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    public function setInUse(bool $inUse): void
    {
        $this->inUse = $inUse;
    }

    public function isInUse(): bool
    {
        return $this->inUse;
    }
}

/**
 * Pool - управляет пулом объектов
 */
class ConnectionPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxConnections;
    private string $host;
    private string $database;
    private string $username;
    private string $password;

    public function __construct(
        int $maxConnections = 10,
        string $host = 'localhost',
        string $database = 'test',
        string $username = 'root',
        string $password = ''
    ) {
        $this->maxConnections = $maxConnections;
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    public function acquire(): DatabaseConnection
    {
        if (count($this->inUse) >= $this->maxConnections) {
            throw new \RuntimeException('Connection pool is exhausted');
        }

        if (!empty($this->available)) {
            /** @var MySQLConnection $connection */
            $connection = array_pop($this->available);
            $connection->setInUse(true);
            $this->inUse[] = $connection;

            return $connection;
        }

        // Создаем новое подключение
        $connection = new MySQLConnection(
            $this->host,
            $this->database,
            $this->username,
            $this->password
        );
        $connection->setInUse(true);
        $this->inUse[] = $connection;

        return $connection;
    }

    public function release(DatabaseConnection $connection): void
    {
        $key = array_search($connection, $this->inUse, true);
        if ($key === false) {
            throw new \RuntimeException('Connection not found in use list');
        }

        unset($this->inUse[$key]);
        $connection->setInUse(false);
        $connection->disconnect(); // Отключаем для безопасности
        $this->available[] = $connection;
    }

    public function getAvailableCount(): int
    {
        return count($this->available);
    }

    public function getInUseCount(): int
    {
        return count($this->inUse);
    }

    public function getTotalCount(): int
    {
        return $this->getAvailableCount() + $this->getInUseCount();
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }
}

/**
 * Пример 2: Pool для потоков (threads)
 */
class Worker
{
    private int $id;
    private bool $busy = false;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->initialize();
    }

    private function initialize(): void
    {
        // Инициализация ресурсоемкая операция
        echo "Initializing worker {$this->id}...\n";
        sleep(1); // Имитация долгой загрузки
    }

    public function process(callable $task): mixed
    {
        $this->busy = true;
        echo "Worker {$this->id} processing task...\n";
        $result = $task();
        $this->busy = false;

        return $result;
    }

    public function isBusy(): bool
    {
        return $this->busy;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

class WorkerPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxWorkers;

    public function __construct(int $maxWorkers = 5)
    {
        $this->maxWorkers = $maxWorkers;
        $this->initializeWorkers();
    }

    private function initializeWorkers(): void
    {
        for ($i = 1; $i <= $this->maxWorkers; $i++) {
            $this->available[] = new Worker($i);
        }
    }

    public function acquire(): Worker
    {
        if (count($this->inUse) >= $this->maxWorkers) {
            throw new \RuntimeException('Worker pool is exhausted');
        }

        if (!empty($this->available)) {
            $worker = array_shift($this->available);
            $this->inUse[] = $worker;

            return $worker;
        }

        // Создаем нового работника (если не достигнут лимит)
        $id = count($this->inUse) + count($this->available) + 1;
        $worker = new Worker($id);
        $this->inUse[] = $worker;

        return $worker;
    }

    public function release(Worker $worker): void
    {
        $key = array_search($worker, $this->inUse, true);
        if ($key === false) {
            throw new \RuntimeException('Worker not found in use list');
        }

        unset($this->inUse[$key]);
        $this->available[] = $worker;
    }

    public function getAvailableCount(): int
    {
        return count($this->available);
    }

    public function getInUseCount(): int
    {
        return count($this->inUse);
    }
}

/**
 * Пример 3: Pool для объектов кэша
 */
class CacheItem
{
    private string $key;
    private mixed $value;
    private int $expiration;

    public function __construct(string $key, mixed $value, int $ttl = 3600)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiration = time() + $ttl;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isExpired(): bool
    {
        return time() > $this->expiration;
    }

    public function touch(int $ttl = 3600): void
    {
        $this->expiration = time() + $ttl;
    }
}

class CachePool
{
    private array $pool = [];
    private int $maxItems;

    public function __construct(int $maxItems = 1000)
    {
        $this->maxItems = $maxItems;
    }

    public function getItem(string $key): ?CacheItem
    {
        if (isset($this->pool[$key])) {
            /** @var CacheItem $item */
            $item = $this->pool[$key];
            if (!$item->isExpired()) {
                $item->touch();

                return $item;
            }
            // Если просрочено, удаляем
            unset($this->pool[$key]);
        }

        return null;
    }

    public function save(CacheItem $item): void
    {
        if (count($this->pool) >= $this->maxItems) {
            // Удаляем самый старый элемент
            $oldestKey = array_key_first($this->pool);
            unset($this->pool[$oldestKey]);
        }

        $this->pool[$item->getKey()] = $item;
    }

    public function delete(string $key): void
    {
        unset($this->pool[$key]);
    }

    public function clear(): void
    {
        $this->pool = [];
    }

    public function count(): int
    {
        return count($this->pool);
    }
}

/**
 * Пример 4: Pool для PDF документов
 */
class PDFDocument
{
    private string $filename;
    private string $content;
    private bool $isGenerated = false;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function generate(string $content): void
    {
        // Долгий процесс генерации PDF
        echo "Generating PDF {$this->filename}...\n";
        sleep(2);
        $this->content = $content;
        $this->isGenerated = true;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }

    public function reset(): void
    {
        $this->content = '';
        $this->isGenerated = false;
    }
}

class PDFPool
{
    private array $available = [];
    private array $inUse = [];
    private int $maxDocuments;

    public function __construct(int $maxDocuments = 5)
    {
        $this->maxDocuments = $maxDocuments;
        $this->initializeDocuments();
    }

    private function initializeDocuments(): void
    {
        for ($i = 1; $i <= $this->maxDocuments; $i++) {
            $this->available[] = new PDFDocument("document_{$i}.pdf");
        }
    }

    public function acquire(): PDFDocument
    {
        if (count($this->inUse) >= $this->maxDocuments) {
            throw new \RuntimeException('PDF pool is exhausted');
        }

        if (!empty($this->available)) {
            $document = array_shift($this->available);
            $this->inUse[] = $document;

            return $document;
        }

        // Создаем новый документ
        $id = count($this->inUse) + count($this->available) + 1;
        $document = new PDFDocument("document_{$id}.pdf");
        $this->inUse[] = $document;

        return $document;
    }

    public function release(PDFDocument $document): void
    {
        $document->reset();
        $key = array_search($document, $this->inUse, true);
        if ($key === false) {
            throw new \RuntimeException('Document not found in use list');
        }

        unset($this->inUse[$key]);
        $this->available[] = $document;
    }

    public function getAvailableCount(): int
    {
        return count($this->available);
    }

    public function getInUseCount(): int
    {
        return count($this->inUse);
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Pool управляет коллекцией объектов, которые можно повторно использовать
 *    Reusable объекты создаются один раз и затем используются многократно
 *    Client запрашивает объекты из пула, использует их, а затем возвращает
 *    Factory создает новые объекты при необходимости (если пул пуст)
 *
 * Преимущества:
 *    Экономия ресурсов — повторное использование дорогостоящих объектов
 *    Улучшение производительности — избегание затрат на создание/уничтожение объектов
 *    Контроль ресурсов — ограничение максимального количества объектов
 *    Управление жизненным циклом — централизованное управление объектами
 *    Стабильность — предотвращение исчерпания ресурсов
 * Недостатки:
 *    Сложность реализации — нужно управлять состоянием объектов
 *    Потенциальные утечки — если объекты не возвращаются в пул
 *    Синхронизация — в многопоточных средах нужна синхронизация
 *    Ограничение гибкости — объекты должны быть переиспользуемыми
 *    Обслуживание — нужно очищать "грязные" или устаревшие объекты
 *
 * Где используется в фреймворках:
 * 1. Laravel's Database Connection Pool
 *    // Laravel использует пул подключений к базе данных
 *    // В реальности это реализовано через DatabaseManager
 *    $connection = DB::connection('mysql');
 *    // Используем подключение
 *    $result = $connection->table('users')->get();
 *    // Подключение возвращается в пул автоматически
 *    // Или с несколькими подключениями
 *    $mysql = DB::connection('mysql');
 *    $pgsql = DB::connection('pgsql');
 *    $sqlite = DB::connection('sqlite');
 *    // Все подключения управляются пулом
 * 2. Symfony's Worker Pool
 *    // Symfony Process Component - пул процессов
 *    use Symfony\Component\Process\Process;
 *    use Symfony\Component\Process\ProcessPool;
 *    $pool = new ProcessPool(5); // Максимум 5 процессов
 *    $processes = [];
 *    for ($i = 0; $i < 10; $i++) {
 *        $process = $pool->acquire();
 *        $process->setCommandLine('php -r "echo \'Hello from process ' . $i . '\';"');
 *        $processes[] = $process;
 *    }
 *    foreach ($processes as $process) {
 *        $process->run();
 *        echo $process->getOutput();
 *        $pool->release($process);
 *    }
 * 3. Yii Framework's Object Pool
 *    // Yii Framework использует пул объектов для компонентов
 *    // В Yii 2 компоненты создаются один раз и затем переиспользуются
 *    // Пример из Yii 2
 *    $cache = Yii::$app->cache;
 *    // Используем кэш
 *    $cache->set('key', 'value', 3600);
 *    $value = $cache->get('key');
 *    // Кэш-компонент переиспользуется в приложении
 *    // Или для DB
 *    $db = Yii::$app->db;
 *    // Используем подключение
 *    $users = $db->createCommand('SELECT * FROM users')->queryAll();
 *    // Подключение управляется пулом
 * 4. Laravel's PDO Statement Pool
 *    // Laravel использует пул подготовленных выражений
 *    // Это реализовано внутри Capsule или DatabaseManager
 *    $query = DB::table('users')->where('active', 1);
 *    // Подготовленное выражение создается один раз
 *    $users = $query->get();
 *    // Выражение возвращается в пул для повторного использования
 *    // Или с raw выражениями
 *    $result = DB::select('SELECT * FROM users WHERE age > ?', [18]);
 *    // Подготовленное выражение переиспользуется
 * 5. Symfony's Mailer Transport Pool
 *    // Symfony Mailer использует пул транспортов
 *    use Symfony\Component\Mailer\Transport;
 *    use Symfony\Component\Mailer\Transport\TransportInterface;
 *    // Создаем пул транспортов
 *    $transports = [
 *        'smtp' => Transport::fromDsn('smtp://user:pass@smtp.example.com'),
 *        'sendmail' => Transport::fromDsn('sendmail://'),
 *    ];
 *    // Используем транспорты из пула
 *    $mailer = new Mailer($transports['smtp']);
 *    $mailer->send(new Email());
 *    // Транспорт возвращается в пул
 * 6. Laravel's Queue Worker Pool
 *    // Laravel Queue использует пул воркеров
 *    // Это реализовано в QueueManager и Worker командой
 *    // artisan queue:work создает пул воркеров
 *    // php artisan queue:work --tries=3 --sleep=5
 *    // Каждый воркер берет задачу из очереди, обрабатывает ее
 *    // и возвращается в пул для следующей задачи
 * 7. Doctrine's Connection Pool
 *    // Doctrine DBAL использует пул подключений
 *    use Doctrine\DBAL\Connection;
 *    use Doctrine\DBAL\PoolingDriver;
 *    $config = new \Doctrine\DBAL\Configuration();
 *    $connectionParams = [
 *        'driver' => 'pdo_mysql',
 *        'host' => 'localhost',
 *        'dbname' => 'test',
 *        'user' => 'root',
 *        'password' => '',
 *        'driverOptions' => [
 *            \PDO::ATTR_PERSISTENT => true, // Использует постоянные подключения
 *        ],
 *    ];
 *    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
 *    // Подключение управляется пулом
 *    // Или с пулом явно
 *    $driver = new \Doctrine\DBAL\PoolingDriver(
 *        new \Doctrine\DBAL\Driver\PDO\MySQLDriver(),
 *        $config
 *    );
 *    $conn = $driver->connect($connectionParams);
 * 8. Laravel's Redis Pool
 *    // Laravel использует пул Redis соединений
 *    // Реализовано в RedisManager и RedisConnector
 *    $redis = Redis::connection();
 *    // Используем Redis
 *    $value = $redis->get('key');
 *    // Соединение возвращается в пул
 *    // Или с несколькими соединениями
 *    $default = Redis::connection('default');
 *    $cache = Redis::connection('cache');
 *    $queue = Redis::connection('queue');
 * 9. Symfony's Event Dispatcher Pool
 *    // Symfony EventDispatcher использует пул слушателей
 *    use Symfony\Component\EventDispatcher\EventDispatcher;
 *    $dispatcher = new EventDispatcher();
 *    // Регистрируем слушатели
 *    $dispatcher->addListener('kernel.request', function (FilterResponseEvent $event) {
 *        // ...
 *    });
 *    // Диспетчер переиспользует слушателей для каждого события
 *    $dispatcher->dispatch(new RequestEvent());
 *    $dispatcher->dispatch(new ResponseEvent());
 * 10. Laravel's Service Instance Pool
 *    // Laravel Service Container использует пул экземпляров
 *    $app = new Illuminate\Foundation\Application();
 *    // Регистрируем синглтон
 *    $app->singleton(Connection::class, function ($app) {
 *        return new Connection($app['config']['database']);
 *    });
 *    // Каждый раз получаем один и тот же экземпляр
 *    $conn1 = $app->make(Connection::class);
 *    $conn2 = $app->make(Connection::class);
 *    // $conn1 === $conn2
 *    // Это разновидность Object Pool для сервисов
 *
 * Когда полезен:
 *    Дорогостоящее создание объектов — когда создание объектов требует много ресурсов
 *    Ограниченные ресурсы — когда есть ограничение на количество объектов
 *    Высокая частота использования — когда объекты используются очень часто
 *    Необходимость контроля — когда нужно управлять жизненным циклом объектов
 *
 * Разница между Singleton и Object Pool:
 *
 * Singleton = Один экземпляр на класс
 *   Глобальный доступ
 *     Один объект на все приложение
 *       Простая реализация
 *         Один объект всегда
 *
 * Object Pool = Несколько экземпляров в пуле
 *   Доступ через пул
 *     Несколько объектов для разных клиентов
 *       Сложная реализация с управлением
 *         Множество объектов, которые переиспользуются
 */

try {
    echo "=== Object Pool Pattern Example ===\n\n";

    // Пример 1: Connection Pool
    echo "=== Database Connection Pool ===\n";

    $pool = new ConnectionPool(
        maxConnections: 3,
        host: 'localhost',
        database: 'test',
        username: 'root',
        password: ''
    );

    $conn1 = $pool->acquire();
    $conn1->connect();
    $result1 = $conn1->query("SELECT 'Hello from pool 1'");

    $conn2 = $pool->acquire();
    $conn2->connect();
    $result2 = $conn2->query("SELECT 'Hello from pool 2'");

    echo "Connection pool stats:\n";
    echo 'Available: ' . $pool->getAvailableCount() . "\n";
    echo 'In use: ' . $pool->getInUseCount() . "\n";
    echo 'Total: ' . $pool->getTotalCount() . "\n";
    echo 'Max: ' . $pool->getMaxConnections() . "\n";

    $pool->release($conn1);
    $pool->release($conn2);

    echo "After release:\n";
    echo 'Available: ' . $pool->getAvailableCount() . "\n";
    echo 'In use: ' . $pool->getInUseCount() . "\n\n";

    // Пример 2: Worker Pool
    echo "=== Worker Pool ===\n";

    $workerPool = new WorkerPool(maxWorkers: 3);

    $task1 = function () {
        sleep(1);

        return 'Task 1 completed';
    };

    $task2 = function () {
        sleep(2);

        return 'Task 2 completed';
    };

    $task3 = function () {
        sleep(1);

        return 'Task 3 completed';
    };

    $worker1 = $workerPool->acquire();
    $result1 = $worker1->process($task1);

    $worker2 = $workerPool->acquire();
    $result2 = $worker2->process($task2);

    $worker3 = $workerPool->acquire();
    $result3 = $worker3->process($task3);

    $workerPool->release($worker1);
    $workerPool->release($worker2);
    $workerPool->release($worker3);

    echo "Worker pool stats:\n";
    echo 'Available: ' . $workerPool->getAvailableCount() . "\n";
    echo 'In use: ' . $workerPool->getInUseCount() . "\n\n";

    // Пример 3: Cache Pool
    echo "=== Cache Pool ===\n";

    $cachePool = new CachePool(maxItems: 5);

    $item1 = new CacheItem('user:1', ['name' => 'John', 'email' => 'john@example.com']);
    $item2 = new CacheItem('product:101', ['name' => 'Laptop', 'price' => 999.99]);
    $item3 = new CacheItem('order:555', ['status' => 'completed', 'total' => 123.45]);

    $cachePool->save($item1);
    $cachePool->save($item2);
    $cachePool->save($item3);

    $cachedItem = $cachePool->getItem('user:1');
    echo 'Cached user: ' . json_encode($cachedItem?->getValue()) . "\n";

    $cachePool->delete('product:101');

    echo 'Cache pool count: ' . $cachePool->count() . "\n\n";

    // Пример 4: PDF Pool
    echo "=== PDF Pool ===\n";

    $pdfPool = new PDFPool(maxDocuments: 2);

    $pdf1 = $pdfPool->acquire();
    $pdf1->generate('This is PDF document 1');

    $pdf2 = $pdfPool->acquire();
    $pdf2->generate('This is PDF document 2');

    echo "PDF pool stats:\n";
    echo 'Available: ' . $pdfPool->getAvailableCount() . "\n";
    echo 'In use: ' . $pdfPool->getInUseCount() . "\n";

    $pdfPool->release($pdf1);
    $pdfPool->release($pdf2);

    echo "After release:\n";
    echo 'Available: ' . $pdfPool->getAvailableCount() . "\n";
    echo 'In use: ' . $pdfPool->getInUseCount() . "\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

/*  php8.4 *********************************************

// Использование атрибутов для автоматической генерации Object Pool
#[ObjectPool(maxObjects: 10)]
class DatabaseConnection
{
    // ...
}

// Генерация Object Pool через атрибуты
class ObjectPoolBuilder
{
    public function build(string $class, int $maxObjects): object
    {
        $reflector = new ReflectionClass($class);
        $pool = new class($class, $maxObjects) {
            private string $class;
            private int $maxObjects;
            private array $available = [];
            private array $inUse = [];

            public function __construct(string $class, int $maxObjects)
            {
                $this->class = $class;
                $this->maxObjects = $maxObjects;
                $this->initialize();
            }

            private function initialize(): void
            {
                for ($i = 0; $i < $this->maxObjects; $i++) {
                    $this->available[] = $this->createObject();
                }
            }

            private function createObject(): object
            {
                $reflector = new ReflectionClass($this->class);
                return $reflector->newInstance();
            }

            public function acquire(): object
            {
                if (count($this->inUse) >= $this->maxObjects) {
                    throw new \RuntimeException("Pool exhausted");
                }

                if (!empty($this->available)) {
                    $object = array_shift($this->available);
                    $this->inUse[] = $object;
                    return $object;
                }

                $object = $this->createObject();
                $this->inUse[] = $object;
                return $object;
            }

            public function release(object $object): void
            {
                $key = array_search($object, $this->inUse, true);
                if ($key === false) {
                    throw new \RuntimeException("Object not in use");
                }

                unset($this->inUse[$key]);
                $this->available[] = $object;
            }
        };

        return $pool;
    }
}

// Pattern matching для автоматического создания Object Pool
public function getPool(string $type, int $maxObjects = 10): object
{
    return match ($type) {
    'database' => new ConnectionPool($maxObjects),
        'worker' => new WorkerPool($maxObjects),
        'cache' => new CachePool($maxObjects),
        'pdf' => new PDFPool($maxObjects),
        'connection' => new ConnectionPool($maxObjects),
        'thread' => new WorkerPool($maxObjects),
        'object' => new GenericObjectPool($maxObjects),
        default => throw new InvalidArgumentException("Unknown pool type"),
    };
}

// Enum для типов Object Pool
enum PoolType: string
{
    case DATABASE = 'database';
    case WORKER = 'worker';
    case CACHE = 'cache';
    case PDF = 'pdf';
    case CONNECTION = 'connection';
    case THREAD = 'thread';
    case OBJECT = 'object';
    case CONNECTION = 'connection';
    case RESOURCE = 'resource';
    case SERVICE = 'service';
}

class PoolFactory
{
    public function create(PoolType $type, int $maxObjects = 10, array $config = []): object
    {
        return match ($type) {
        PoolType::DATABASE => new ConnectionPool(
        maxConnections: $maxObjects,
                host: $config['host'] ?? 'localhost',
                database: $config['database'] ?? 'test',
                username: $config['username'] ?? 'root',
                password: $config['password'] ?? ''
            ),
            PoolType::WORKER => new WorkerPool(maxWorkers: $maxObjects),
            PoolType::CACHE => new CachePool(maxItems: $maxObjects),
            PoolType::PDF => new PDFPool(maxDocuments: $maxObjects),
            PoolType::CONNECTION => new ConnectionPool(maxConnections: $maxObjects),
            PoolType::THREAD => new WorkerPool(maxWorkers: $maxObjects),
            PoolType::OBJECT => new GenericObjectPool($maxObjects),
            PoolType::RESOURCE => new ResourcePool($maxObjects),
            PoolType::SERVICE => new ServicePool($maxObjects),
        };
    }
}

********************************** */

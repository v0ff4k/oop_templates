<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 18.08.26 - 20:41
 */

namespace Creational\Singleton;

use \PDO;
use \PDOException;
use \RuntimeException;

/**
 * Singleton для управления несколькими соединениями с БД
 * с профилированием и кэшированием схем
Мультидатабасный менеджер с профилированием
 */
final class DatabaseManager
{
    private static ?DatabaseManager $instance = null;

    /**
     * @var PDO[] Активные соединения
     */
    private array $connections = [];

    /**
     * @var array Настройки соединений
     */
    private array $configs = [];

    /**
     * @var array Кэш структур таблиц
     */
    private array $schemaCache = [];

    /**
     * @var float[] Профилирование времени запросов
     */
    private array $queryProfiles = [];

    /**
     * @var int Количество выполненных запросов
     */
    private int $queryCount = 0;

    /**
     * Приватный конструктор для предотвращения создания извне
     */
    private function __construct()
    {
        // Загрузка конфигураций из файлов
        $this->loadConfigs();
    }

    /**
     * Запрет клонирования
     */
    private function __clone()
    {
        throw new RuntimeException('Singleton cannot be cloned');
    }

    /**
     * Запрет десериализации
     */
    public function __wakeup(): void
    {
        throw new RuntimeException('Singleton cannot be deserialized');
    }

    /**
     * Получение экземпляра Singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Загрузка конфигураций из файлов
     */
    private function loadConfigs(): void
    {
        $configFiles = [
            __DIR__ . '/configs/mysql.php',
            __DIR__ . '/configs/postgresql.php',
            __DIR__ . '/configs/redis.php',
        ];

        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $config = require $file;
                $this->configs[$config['name']] = $config;
            }
        }
    }

    /**
     * Получение соединения с БД
     */
    public function getConnection(string $connectionName): PDO
    {
        if (!isset($this->connections[$connectionName])) {
            if (!isset($this->configs[$connectionName])) {
                throw new RuntimeException("Connection '$connectionName' not configured");
            }

            $config = $this->configs[$connectionName];

            try {
                $pdo = new PDO(
                    $config['dsn'],
                    $config['username'],
                    $config['password'],
                    $config['options'] ?? []
                );

                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                $this->connections[$connectionName] = $pdo;

            } catch (PDOException $e) {
                throw new RuntimeException(
                    "Failed to connect to {$connectionName}: " . $e->getMessage()
                );
            }
        }

        return $this->connections[$connectionName];
    }

    /**
     * Выполнение запроса с профилированием
     */
    public function query(string $connectionName, string $sql, array $params = []): array
    {
        $startTime = microtime(true);

        $pdo = $this->getConnection($connectionName);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetchAll();
        // unset($pdo, $stmt); // <-- will auto close DB connection!

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Профилирование
        $this->queryProfiles[] = [
            'connection' => $connectionName,
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->queryCount++;

        return $result;
    }

    /**
     * Получение структуры таблицы (с кэшированием)
     */
    public function getTableSchema(string $connectionName, string $table): array
    {
        $cacheKey = $connectionName . '.' . $table;

        if (!isset($this->schemaCache[$cacheKey])) {
            $sql = "DESCRIBE `$table`";
            $result = $this->query($connectionName, $sql);
            $this->schemaCache[$cacheKey] = $result;
        }

        return $this->schemaCache[$cacheKey];
    }

    /**
     * Получение статистики запросов
     */
    public function getQueryStats(): array
    {
        return [
            'total_queries' => $this->queryCount,
            'avg_duration' => $this->queryCount > 0
                ? array_sum(array_column($this->queryProfiles, 'duration')) / $this->queryCount
                : 0,
            'slowest_query' => $this->queryCount > 0
                ? max(array_column($this->queryProfiles, 'duration'))
                : 0,
            'profiles' => $this->queryProfiles,
        ];
    }

    /**
     * Очистка кэша схем
     */
    public function clearSchemaCache(): void
    {
        $this->schemaCache = [];
    }

    /**
     * Закрытие всех соединений
     */
    public function closeAllConnections(): void
    {
        $this->connections = [];
    }
}

/**
 * Пример конфигурационных файлов
 *
 * configs/mysql.php:
 * <?php return ['name' => 'mysql', 'dsn' => 'mysql:host=localhost;dbname=test', 'username' => 'root', 'password' => ''];
 *
 * configs/postgresql.php:
 * <?php return ['name' => 'postgresql', 'dsn' => 'pgsql:host=localhost;dbname=test', 'username' => 'postgres', 'password' => ''];
 */


/**
 * Пример использования
 */

try {
    echo "=== Singleton Database Manager Example ===\n\n";

    $db = DatabaseManager::getInstance();

    // Выполнение запросов к разным БД
    $mysqlResult = $db->query(
        'mysql',
        'SELECT COUNT(*) as count FROM users'
    );
    $pgResult = $db->query(
        'postgresql',
        'SELECT COUNT(*) as count FROM users'
    );

    echo 'MySQL users: ' . ($mysqlResult[0]['count'] ?? 0) . "\n";
    echo 'PostgreSQL users: ' . ($pgResult[0]['count'] ?? 0) . "\n";

    // Получение структуры таблицы с кэшированием
    $schema = $db->getTableSchema('mysql', 'users');
    echo "Table 'users' has " . count($schema) . " columns\n";

    // Профилирование
    $stats = $db->getQueryStats();
    echo 'Total queries: ' . $stats['total_queries'] . "\n";
    echo 'Average duration: ' . number_format($stats['avg_duration'], 4) . " sec\n";

    // Очистка кэша
    $db->clearSchemaCache();

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 18.08.26 - 21:50
 */

namespace Creational\Singleton;

use PDO;
use RuntimeException;

/**
 * Singleton для кэширования структур БД
 * Снижает нагрузку на БД при частых запросах метаданных
Менеджер кэша схемы БД
 */
final class SchemaCache
{
    private static ?SchemaCache $instance = null;

    /**
     * @var array Кэш структур таблиц
     */
    private array $cache = [];

    /**
     * @var int Время жизни кэша в секундах
     */
    private int $ttl = 3600;

    /**
     * @var string Путь к кэш файлу
     */
    private string $cacheFile;

    /**
     * Приватный конструктор
     */
    private function __construct(string $cacheDir)
    {
        $this->cacheFile = $cacheDir . '/schema_cache.php';
        $this->loadFromCache();
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
     * Получение экземпляра
     */
    public static function getInstance(string $cacheDir = ''): self
    {
        if (self::$instance === null) {
            if (empty($cacheDir)) {
                $cacheDir = sys_get_temp_dir();
            }
            self::$instance = new self($cacheDir);
        }

        return self::$instance;
    }

    /**
     * Загрузка кэша из файла
     */
    private function loadFromCache(): void
    {
        if (file_exists($this->cacheFile) &&
            (time() - filemtime($this->cacheFile)) < $this->ttl) {
            $this->cache = include $this->cacheFile;
        }
    }

    /**
     * Сохранение кэша в файл
     */
    private function saveToCache(): void
    {
        $content = "<?php\n\nreturn " . var_export($this->cache, true) . ";\n";
        file_put_contents($this->cacheFile, $content);
    }

    /**
     * Получение структуры таблицы
     */
    public function getTableSchema(PDO $connection, string $table): array
    {
        $cacheKey = $this->generateCacheKey($connection, $table);

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->fetchSchemaFromDb($connection, $table);
            $this->saveToCache();
        }

        return $this->cache[$cacheKey];
    }

    /**
     * Генерация уникального ключа кэша
     */
    private function generateCacheKey(PDO $connection, string $table): string
    {
        $dsn = $connection->getAttribute(PDO::ATTR_DSN);
        return md5($dsn . '.' . $table);
    }

    /**
     * Получение структуры таблицы из БД
     */
    private function fetchSchemaFromDb(PDO $connection, string $table): array
    {
        $database = $connection->query('SELECT DATABASE()')->fetchColumn();

        // Для MySQL
        if (strpos($connection->getAttribute(PDO::ATTR_DRIVER_NAME), 'mysql') !== false) {
            $sql = "DESCRIBE `$database`.`$table`";
        }
        // Для PostgreSQL
        elseif (strpos($connection->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false) {
            $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ?";
        }
        // Для SQLite
        else {
            $sql = "PRAGMA table_info($table)";
        }

        $stmt = $connection->prepare($sql);

        if (strpos($connection->getAttribute(PDO::ATTR_DRIVER_NAME), 'pgsql') !== false) {
            $stmt->execute([$table]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    /**
     * Очистка кэша для конкретной таблицы
     */
    public function clearTableCache(PDO $connection, string $table): void
    {
        $cacheKey = $this->generateCacheKey($connection, $table);
        unset($this->cache[$cacheKey]);
        $this->saveToCache();
    }

    /**
     * Очистка всего кэша
     */
    public function clearAllCache(): void
    {
        $this->cache = [];
        $this->saveToCache();
    }

    /**
     * Получение статистики кэша
     */
    public function getCacheStats(): array
    {
        return [
            'total_entries' => count($this->cache),
            'cache_file' => $this->cacheFile,
            'cache_size' => filesize($this->cacheFile),
            'ttl' => $this->ttl,
        ];
    }
}

/**
 * Пример использования
 */

try {
    echo "=== Singleton Schema Cache Example ===\n\n";

    $schemaCache = SchemaCache::getInstance(__DIR__ . '/cache');

    // Получение соединения (например, из DatabaseManager)
    $db = DatabaseManager::getInstance();
    $pdo = $db->getConnection('mysql');

    // Первый запрос - из БД
    $schema1 = $schemaCache->getTableSchema($pdo, 'users');
    echo 'First query: ' . count($schema1) . " columns\n";

    // Второй запрос - из кэша
    $schema2 = $schemaCache->getTableSchema($pdo, 'users');
    echo 'Second query: ' . count($schema2) . " columns (from cache)\n";

    // Статистика кэша
    $stats = $schemaCache->getCacheStats();
    echo 'Cache entries: ' . $stats['total_entries'] . "\n";
    echo 'Cache file: ' . $stats['cache_file'] . "\n";

    // Очистка кэша
    // $schemaCache->clearTableCache($pdo, 'users');
    // $schemaCache->clearAllCache();

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

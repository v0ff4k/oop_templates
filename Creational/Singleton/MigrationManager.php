<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 18.08.26 - 20:24
 */

namespace Creational\Singleton;

use \PDO;
use \RuntimeException;

/**
 * Singleton для управления миграциями БД
 * с поддержкой транзакций и версионированием.
 *
 * Менеджер миграций БД с транзакциями
 */
final class MigrationManager
{
    private static ?MigrationManager $instance = null;

    /**
     * @var PDO Соединение с БД
     */
    private PDO $connection;

    /**
     * @var string Текущая версия БД
     */
    private string $currentVersion = '0.0.0';

    /**
     * @var string Папка с миграциями
     */
    private string $migrationsDir;

    /**
     * @var array Применённые миграции
     */
    private array $appliedMigrations = [];

    /**
     * Приватный конструктор
     */
    private function __construct(string $migrationsDir)
    {
        $this->migrationsDir = rtrim($migrationsDir, '/\\');
        $this->initialize();
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
    public static function getInstance(string $migrationsDir = ''): self
    {
        if (self::$instance === null) {
            if (empty($migrationsDir)) {
                throw new RuntimeException('Migrations directory must be provided');
            }
            self::$instance = new self($migrationsDir);
        }

        return self::$instance;
    }

    /**
     * Инициализация менеджера
     */
    private function initialize(): void
    {
        // Создание таблицы для отслеживания миграций
        $this->createMigrationsTable();

        // Загрузка текущей версии
        $this->loadCurrentVersion();

        // Загрузка применённых миграций
        $this->loadAppliedMigrations();
    }

    /**
     * Создание таблицы миграций
     */
    private function createMigrationsTable(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS migrations (
    version VARCHAR(20) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    checksum VARCHAR(32) NOT NULL
);
SQL;

        $this->getConnection()->exec($sql);
    }

    /**
     * Получение соединения с БД
     */
    private function getConnection(): PDO
    {
        if (!isset($this->connection)) {
            // Здесь должна быть логика получения соединения
            // Например, через DatabaseManager::getInstance()
            throw new RuntimeException('Connection not set');
        }

        return $this->connection;
    }

    /**
     * Установка соединения
     */
    public function setConnection(PDO $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Загрузка текущей версии БД
     */
    private function loadCurrentVersion(): void
    {
        $sql = 'SELECT MAX(version) as max_version FROM migrations';
        $result = $this->getConnection()->query($sql)->fetch();

        if ($result && $result['max_version']) {
            $this->currentVersion = $result['max_version'];
        }
    }

    /**
     * Загрузка применённых миграций
     */
    private function loadAppliedMigrations(): void
    {
        $sql = 'SELECT version, name, applied_at, checksum FROM migrations ORDER BY version';
        $result = $this->getConnection()->query($sql)->fetchAll();

        $this->appliedMigrations = array_map(function ($row) {
            return [
                'version' => $row['version'],
                'name' => $row['name'],
                'applied_at' => $row['applied_at'],
                'checksum' => $row['checksum'],
            ];
        }, $result);
    }

    /**
     * Получение списка доступных миграций
     */
    public function getAvailableMigrations(): array
    {
        $migrations = [];
        $files = glob($this->migrationsDir . '/migrations/*.php');

        foreach ($files as $file) {
            // Извлечение номера версии из имени файла
            if (preg_match('/(\d+\.\d+\.\d+)/', basename($file), $matches)) {
                $version = $matches[1];
                $migrations[$version] = [
                    'file' => $file,
                    'name' => basename($file, '.php'),
                    'version' => $version,
                ];
            }
        }

        ksort($migrations);

        return $migrations;
    }

    /**
     * Применение миграций
     */
    public function migrate(): void
    {
        $available = $this->getAvailableMigrations();
        $connection = $this->getConnection();

        // Начинаем транзакцию
        $connection->beginTransaction();

        try {
            foreach ($available as $version => $migration) {
                if (version_compare($version, $this->currentVersion, '>')) {
                    // Подключение миграции
                    require_once $migration['file'];

                    // Имя класса миграции (стандартное соглашение)
                    $className = '\\Migrations\\' . str_replace(['.', '-'], '', $version);

                    if (!class_exists($className)) {
                        throw new RuntimeException("Migration class $className not found");
                    }

                    /** @var MigrationInterface $migrationObj */
                    $migrationObj = new $className();
                    $migrationObj->up($connection);

                    // Сохранение информации о миграции
                    $checksum = md5_file($migration['file']);
                    $this->saveMigration($version, $migration['name'], $checksum);

                    echo "Applied migration: $version - {$migration['name']}\n";
                }
            }

            // Фиксация транзакции
            $connection->commit();

            // Обновление текущей версии
            $this->loadCurrentVersion();

        } catch (\Throwable $e) {
            // Откат транзакции при ошибке
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Сохранение информации о миграции
     */
    private function saveMigration(string $version, string $name, string $checksum): void
    {
        $sql = 'INSERT INTO migrations (version, name, checksum) VALUES (?, ?, ?)';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$version, $name, $checksum]);
    }

    /**
     * Откат последней миграции
     */
    public function rollback(): void
    {
        if (empty($this->appliedMigrations)) {
            return;
        }

        // Берем последнюю миграцию
        $lastMigration = end($this->appliedMigrations);
        $version = $lastMigration['version'];

        $files = glob($this->migrationsDir . '/migrations/*.php');

        foreach ($files as $file) {
            if (preg_match('/(\d+\.\d+\.\d+)/', basename($file), $matches) && $matches[1] === $version) {
                require_once $file;
                $className = '\\Migrations\\' . str_replace(['.', '-'], '', $version);

                if (class_exists($className)) {
                    /** @var MigrationInterface $migrationObj */
                    $migrationObj = new $className();
                    $migrationObj->down($this->getConnection());

                    // Удаление записи из таблицы миграций
                    $sql = 'DELETE FROM migrations WHERE version = ?';
                    $stmt = $this->getConnection()->prepare($sql);
                    $stmt->execute([$version]);

                    echo "Rolled back migration: $version\n";
                }
            }
        }

        // Обновление текущей версии
        $this->loadCurrentVersion();
    }

    /**
     * Получение текущей версии БД
     */
    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    /**
     * Получение списка применённых миграций
     */
    public function getAppliedMigrations(): array
    {
        return $this->appliedMigrations;
    }
}

/**
 * Интерфейс для миграций
 */
interface MigrationInterface
{
    public function up(PDO $connection): void;
    public function down(PDO $connection): void;
}

/**
 * Пример миграции
 *
 * Файл: migrations/1.0.0_create_users_table.php
 *
 * <?php
 *
 * namespace Migrations;
 *
 * use PDO;
 *
 * class CreateUsersTable implements MigrationInterface
 * {
 *     public function up(PDO $connection): void
 *     {
 *         $sql = <<<SQL
 * CREATE TABLE users (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     email VARCHAR(255) UNIQUE NOT NULL,
 *     name VARCHAR(255) NOT NULL,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * );
 * SQL;
 *
 *         $connection->exec($sql);
 *     }
 *
 *     public function down(PDO $connection): void
 *     {
 *         $connection->exec("DROP TABLE users");
 *     }
 * }
 */


/**
 * Пример использования
 */

try {
    echo "=== Singleton Migration Manager Example ===\n\n";

    // Инициализация менеджера миграций
    $migrationManager = MigrationManager::getInstance(__DIR__ . '/migrations');

    // Установка соединения (например, из DatabaseManager)
    $db = DatabaseManager::getInstance();
    $migrationManager->setConnection($db->getConnection('mysql'));

    // Применение миграций
    echo 'Current version: ' . $migrationManager->getCurrentVersion() . "\n";

    $migrationManager->migrate();

    echo 'New version: ' . $migrationManager->getCurrentVersion() . "\n";

    // Получение списка применённых миграций
    $applied = $migrationManager->getAppliedMigrations();

    echo "Applied migrations:\n";

    foreach ($applied as $migration) {
        echo "  - {$migration['version']}: {$migration['name']}\n";
    }

    // Откат последней миграции
    // $migrationManager->rollback();

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

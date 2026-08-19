<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 18.08.26 - 22:03
 */

namespace Creational\Singleton;

use \PDO;
use \RuntimeException;
use \PDOException;

/**
 * Singleton для управления распределёнными транзакциями
 * Поддержка нескольких БД в рамках одной транзакции
 *
 * Менеджер распределённых транзакций
 */
final class DistributedTransactionManager
{
    private static ?DistributedTransactionManager $instance = null;

    /**
     * @var PDO[] Активные соединения
     */
    private array $connections = [];

    /**
     * @var bool Флаг активной транзакции
     */
    private bool $inTransaction = false;

    /**
     * @var string Идентификатор транзакции
     */
    private string $transactionId;

    /**
     * @var array Лог транзакции
     */
    private array $transactionLog = [];

    /**
     * Приватный конструктор
     */
    private function __construct()
    {
        $this->transactionId = uniqid('tx_', true);
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
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Начало распределённой транзакции
     */
    public function beginTransaction(PDO $connection): void
    {
        if ($this->inTransaction) {
            throw new RuntimeException('Transaction already started');
        }

        $this->connections[] = $connection;
        $connection->beginTransaction();

        $this->inTransaction = true;
        $this->log("Transaction {$this->transactionId} started");
    }

    /**
     * Добавление соединения к активной транзакции
     */
    public function addConnection(PDO $connection): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException('No active transaction');
        }

        if (!in_array($connection, $this->connections, true)) {
            $connection->beginTransaction();
            $this->connections[] = $connection;
            $this->log("Added connection to transaction {$this->transactionId}");
        }
    }

    /**
     * Выполнение запроса в рамках транзакции
     */
    public function execute(string $sql, array $params = [], PDO $connection = null): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException('No active transaction');
        }

        $conn = $connection ?? $this->connections[0];

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $this->log("Executed: $sql | Params: " . json_encode($params));
    }

    /**
     * Фиксация транзакции
     */
    public function commit(): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException('No active transaction');
        }

        foreach ($this->connections as $connection) {
            try {
                $connection->commit();
                $this->log('Committed connection');
            } catch (PDOException $e) {
                $this->log('Commit error: ' . $e->getMessage());
                throw $e;
            }
        }

        $this->inTransaction = false;
        $this->log("Transaction {$this->transactionId} committed");

        $this->cleanup();
    }

    /**
     * Откат транзакции
     */
    public function rollback(): void
    {
        if (!$this->inTransaction) {
            throw new RuntimeException('No active transaction');
        }

        foreach ($this->connections as $connection) {
            try {
                $connection->rollBack();
                $this->log('Rolled back connection');
            } catch (PDOException $e) {
                $this->log('Rollback error: ' . $e->getMessage());
            }
        }

        $this->inTransaction = false;
        $this->log("Transaction {$this->transactionId} rolled back");

        $this->cleanup();
    }

    /**
     * Логирование транзакции
     */
    private function log(string $message): void
    {
        $this->transactionLog[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
        ];
    }

    /**
     * Получение лога транзакции
     */
    public function getTransactionLog(): array
    {
        return $this->transactionLog;
    }

    /**
     * Очистка после транзакции
     */
    private function cleanup(): void
    {
        $this->connections = [];
        $this->transactionLog = [];
        $this->transactionId = uniqid('tx_', true);
    }

    /**
     * Получение статуса транзакции
     */
    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }
}


/**
 * Клиентский код
 *
 * Ключевые преимущества Singleton для работы с БД:
 *    Контроль ресурсов — только один экземпляр управляет соединениями
 *    Глобальная доступность — доступ из любого места приложения
 *    Централизованное управление — все настройки и состояния в одном месте
 *    Эффективность — повторное использование соединений, кэширование
 *    Согласованность — единые правила для всех операций с БД
 *
 * Когда использовать:
 *    Управление соединениями — пул соединений, повторное использование
 *    Конфигурация — единые настройки для всего приложения
 *    Кэширование — централизованный кэш метаданных БД
 *    Транзакции — управление распределёнными транзакциями
 *    Миграции — версионирование и применение изменений схемы
 *
 * Когда НЕ использовать:
 *    Тестирование — глобальное состояние затрудняет тестирование
 *    Масштабирование — Singleton может стать узким местом
 *    Многопоточность — требует синхронизации в многопоточной среде
 *    Конфигурация окружения — разные окружения могут требовать разных экземпляров
 *
 *
 * Основные области применения Singleton в фреймворках:
 *    Подключение к БД
 *    Конфигурационные менеджеры
 *    Кэши
 *    Логирование
 *    Сессии
 *    Event-менеджеры
 *
 * Где используется в фреймворках
 * 1. Laravel's Database Manager
 *    // Singleton для управления соединениями с БД
 *    $mysql = DB::connection('mysql');
 *    // Возвращает Illuminate\Database\MySqlConnection
 *    $postgres = DB::connection('pgsql'); //  Illuminate\Database\PostgresConnection
 *    // Все соединения управляются через один экземпляр
 *    DB::purge('mysql'); // Очистка соединения
 * 2. Laravel's Config Manager
 *    // Singleton для управления конфигурацией
 *    $value = Config::get('app.timezone'); // Возвращает 'UTC'
 *    Config::set('app.timezone', 'America/New_York');
 *    // Устанавливает значение глобально
 *    // Все изменения конфигурации идут через один экземпляр
 * 3. Laravel's Cache Manager
 *    // Singleton для кэширования
 *    Cache::put('key', 'value', 60); // Хранение на 60 минут
 *    $value = Cache::get('key'); // Возвращает 'value'
 *    // Поддержка разных драйверов кэша через один менеджер
 *    Cache::store('redis')->put('key', 'value', 60);
 * 4. Laravel's Session Manager
 *    // Singleton для управления сессиями
 *    Session::put('user_id', 123); // Устанавливает значение в сессии
 *    $user_id = Session::get('user_id'); // Возвращает 123
 *    // Все сессии управляются через один экземпляр
 * 5. Laravel's Event Manager
 *    // Singleton для событий
 *    Event::dispatch(new OrderShipped($order)); // Мониторит событие всем слушателям
 *    Event::listen(function (OrderShipped $event) { // ... // Обработка события
 *    });
 *    // Все события проходят через один диспетчер
 * 6. Symfony's Service Container
 *    // Singleton для сервисов
 *    $router = $container->get('router'); // Symfony\Component\Routing\Router
 *    $logger = $container->get('logger'); // Symfony\Bridge\Monolog\Logger
 *    // Все сервисы управляются через один контейнер
 * 7. Symfony's Request Stack
 *    // Singleton для HTTP запросов
 *    $request = Request::createFromGlobals(); // Symfony\Component\HttpFoundation\Request
 *    $stack = new RequestStack();
 *    $stack->push($request);
 *    // Управление стеком запросов через один экземпляр
 * 8. Yii Framework's Application
 *    // Singleton для приложения
 *    Yii::$app->db->createCommand('SELECT * FROM users')->query(); // yii\db\Command
 *    Yii::$app->cache->get('key'); // Возвращает кэшированное значение
 *    // Все компоненты доступны через один экземпляр Yii::$app
 * 9. CakePHP's Connection Manager
 *    // Singleton для соединений с БД
 *    $conn = ConnectionManager::get('default'); // Cake\Database\Connection
 *    $results = $conn->execute('SELECT * FROM users')->fetchAll();
 *    // Все соединения управляются через один менеджер
 * 10. CodeIgniter's Super Object
 *    // Singleton для всего приложения
 *    $this->db->get('users'); // CodeIgniter\Database
 *    $this->load->library('email');
 *    // Загрузка библиотек через один экземпляр
 *    // Все ресурсы доступны через $this в контроллерах
 * 11. Zend Framework's Service Manager
 *    // Singleton для сервисов
 *    $serviceManager = $container->get(ServiceManager::class);
 *    $logger = $serviceManager->get(LoggerInterface::class);
 *    // Возвращает экземпляр логгера
 *    // Все сервисы создаются и управляются через один менеджер
 * 12. Laravel's Queue Manager
 *    // Singleton для управления очередями
 *    Queue::push(new SendEmail($email)); // Добавляет задачу в очередь
 *    Queue::connection('redis')->push('Job@handle', ['data']);
 *    // Использование разных соединений через один менеджер
 * 13. Laravel's Notification System
 *    // Singleton для уведомлений
 *    $user->notify(new OrderShipped($order)); // - уведомление пользователю
 *    Notification::send($users, new InvoicePaid($invoice)); // - Массовое уведомление
 * 14. Symfony's Mailer
 *    // Singleton для отправки email
 *    $email = (new Email())
 *        ->from('sender@example.com')
 *        ->to('recipient@example.com')
 *        ->subject('Hello')
 *        ->text('Text content');
 *    $mailer->send($email); //  - Вся отправка, идет через 1 mailer
 * 15. Laravel's Filesystem Manager
 *    // Singleton для файловой системы
 *    Storage::disk('s3')->put('file.jpg', $contents); // Сохраняет файл в S3
 *    Storage::disk('local')->path('file.jpg'); // - путь к локальному файлу
 *    // Поддержка разных дисков через один менеджер
 * 16. Laravel's Auth Manager
 *    // Singleton для аутентификации
 *    Auth::login($user); // Аутентифицирует пользователя
 *    $user = Auth::user(); // Возвращает текущего пользователя
 *    // Все операции аутентификации через один экземпляр
 * 17. Symfony's Security Context
 *    // Singleton для безопасности
 *    $token = $this->get('security.token_storage')->getToken(); // - токен безопасности
 *    $user = $this->get('security.context')->getToken()->getUser(); // Получение текущего пользователя
 * 18. Yii Framework's User Component
 *    // Singleton для управления пользователями
 *    Yii::$app->user->login($identity, 3600); // Аутентификация пользователя
 *    Yii::$app->user->logout(); // Выход пользователя
 *    // Все операции с пользователями через один компонент
 * 19. Laravel's Translation Manager
 *    // Singleton для локализации
 *    echo trans('messages.welcome'); // Возвращает переведённое сообщение
 *    trans()->setLocale('ru'); // Устанавливает локаль глобально
 * 20. Symfony's Translator
 *    // Singleton для перевода
 *    $translated = $this->get('translator')->trans('Symfony is great');
 *    // Возвращает переведённую строку
 *    $this->get('translator')->setLocale('fr'); // Устанавливает французскую локаль
 */


try {
    echo "=== Singleton Distributed Transaction Manager Example ===\n\n";

    $txManager = DistributedTransactionManager::getInstance();

    // Получение соединений (например, из DatabaseManager)
    $db = DatabaseManager::getInstance();
    $mysql = $db->getConnection('mysql');
    $postgresql = $db->getConnection('postgresql');

    // Начало транзакции с MySQL
    $txManager->beginTransaction($mysql);

    // Добавление PostgreSQL к транзакции
    $txManager->addConnection($postgresql);

    // Выполнение запросов в обеих БД
    $txManager->execute(
        'UPDATE users SET balance = balance - 100 WHERE id = ?',
        [1],
        $mysql
    );

    $txManager->execute(
        'UPDATE accounts SET amount = amount + 100 WHERE user_id = ?',
        [1],
        $postgresql
    );

    // Фиксация транзакции
    $txManager->commit();

    echo "Distributed transaction committed successfully!\n";

    // Проверка лога
    $log = $txManager->getTransactionLog();
    foreach ($log as $entry) {
        echo "  {$entry['timestamp']}: {$entry['message']}\n";
    }

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";

    // Откат при ошибке
    if ($txManager->isInTransaction()) {
        try {
            $txManager->rollback();
            echo "Transaction rolled back\n";
        } catch (\Exception $rollbackException) {
            echo 'Rollback failed: ' . $rollbackException->getMessage() . "\n";
        }
    }
}

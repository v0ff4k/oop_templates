<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 31.07.2026 - 17:39
 */

namespace Structural\Facade;

use Exception;
use FilesystemIterator;
use InvalidArgumentException;
use PDO;
use PDOStatement;
use RuntimeException;

/**
 * ComplexSubsystem - сложная подсистема
 * Здесь может быть сложная бизнес-логика, работа с БД, API и т.д.
 */
class ComplexSubsystem
{
    public function executeStep1(): void
    {
        echo "Step 1: Initializing system...\n";
    }

    public function executeStep2(): void
    {
        echo "Step 2: Validating input data...\n";
    }

    public function executeStep3(): void
    {
        echo "Step 3: Processing business logic...\n";
    }

    public function executeStep4(): void
    {
        echo "Step 4: Saving to database...\n";
    }

    public function executeStep5(): void
    {
        echo "Step 5: Sending notifications...\n";
    }

    public function executeStep6(): void
    {
        echo "Step 6: Generating reports...\n";
    }

    public function executeStep7(): void
    {
        echo "Step 7: Cleaning up resources...\n";
    }

    public function complexOperation(string $input): array
    {
        echo "Complex operation started with input: $input\n";

        // Имитация сложной логики
        sleep(1);
        $result = [
            'status' => 'success',
            'input' => $input,
            'processed' => strtoupper($input),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        echo "Complex operation completed\n";

        return $result;
    }
}

/**
 * Пример 1: Facade - упрощенный интерфейс к сложной подсистеме
 */
class SystemFacade
{
    private ComplexSubsystem $subsystem;

    public function __construct(ComplexSubsystem $subsystem)
    {
        $this->subsystem = $subsystem;
    }

    /**
     * Упрощенный метод для выполнения основной операции
     */
    public function processOrder(string $orderId, array $items): array
    {
        echo "=== Order Processing Started ===\n";

        // Шаг 1: Инициализация
        $this->subsystem->executeStep1();

        // Шаг 2: Валидация
        $this->subsystem->executeStep2();

        // Шаг 3: Бизнес-логика
        $this->subsystem->executeStep3();

        // Шаг 4: Сохранение
        $this->subsystem->executeStep4();

        // Шаг 5: Уведомления
        $this->subsystem->executeStep5();

        // Шаг 6: Отчеты
        $this->subsystem->executeStep6();

        // Шаг 7: Очистка
        $this->subsystem->executeStep7();

        echo "=== Order Processing Completed ===\n";

        // Возвращаем результат
        return $this->subsystem->complexOperation("Order $orderId");
    }

    /**
     * Упрощенный метод для другого сценария
     */
    public function generateReport(string $reportType, string $period): array
    {
        echo "=== Report Generation Started ===\n";

        $this->subsystem->executeStep1();
        $this->subsystem->executeStep2();
        $this->subsystem->executeStep6();
        $this->subsystem->executeStep7();

        echo "=== Report Generation Completed ===\n";

        return $this->subsystem->complexOperation('Report ' . $reportType . ' for period ' . $period);
    }

    /**
     * Упрощенный метод для API запроса
     */
    public function callExternalApi(string $endpoint, array $params): array
    {
        echo "=== API Call Started ===\n";

        $this->subsystem->executeStep1();
        $this->subsystem->executeStep2();
        $this->subsystem->executeStep3();
        $this->subsystem->executeStep7();

        echo "=== API Call Completed ===\n";

        return $this->subsystem->complexOperation("API $endpoint with params " . json_encode($params));
    }
}

/**
 * Пример 2: Facade для работы с файлами
 */
class FileManager
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Упрощенная операция: создание файла
     */
    public function createFile(string $path, string $content): bool
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');
        $dir = dirname($fullPath);

        // Создаем директорию если не существует
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return file_put_contents($fullPath, $content) !== false;
    }

    /**
     * Упрощенная операция: чтение файла
     */
    public function readFile(string $path): string
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');

        return file_get_contents($fullPath);
    }

    /**
     * Упрощенная операция: копирование файла
     */
    public function copyFile(string $source, string $destination): bool
    {
        $sourcePath = $this->basePath . '/' . ltrim($source, '/');
        $destPath = $this->basePath . '/' . ltrim($destination, '/');
        $destDir = dirname($destPath);

        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        return copy($sourcePath, $destPath);
    }

    /**
     * Упрощенная операция: удаление файла
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = $this->basePath . '/' . ltrim($path, '/');

        return unlink($fullPath);
    }

    /**
     * Упрощенная операция: переименование файла
     */
    public function renameFile(string $oldPath, string $newPath): bool
    {
        $oldFullPath = $this->basePath . '/' . ltrim($oldPath, '/');
        $newFullPath = $this->basePath . '/' . ltrim($newPath, '/');
        $newDir = dirname($newFullPath);

        if (!is_dir($newDir)) {
            mkdir($newDir, 0777, true);
        }

        return rename($oldFullPath, $newFullPath);
    }

    /**
     * Упрощенная операция: получение списка файлов
     */
    public function listFiles(string $directory = ''): array
    {
        $fullPath = $this->basePath . '/' . ltrim($directory, '/');
        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];
        $iterator = new FilesystemIterator($fullPath);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getFilename();
            }
        }

        return $files;
    }
}

/**
 * Пример 3: Facade для работы с базой данных
 */
class DatabaseFacade
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Упрощенная операция: вставка записи
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn ($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Упрощенная операция: обновление записи
     */
    public function update(string $table, array $data, array $conditions): int
    {
        $setClause = implode(', ', array_map(fn ($col) => "$col = :$col", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn ($col) => "$col = :where_$col", array_keys($conditions)));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            $setClause,
            $whereClause
        );

        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        foreach ($conditions as $key => $value) {
            $params[':where_' . $key] = $value;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Упрощенная операция: удаление записи
     */
    public function delete(string $table, array $conditions): int
    {
        $whereClause = implode(' AND ', array_map(fn ($col) => "$col = :$col", array_keys($conditions)));

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            $whereClause
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);

        return $stmt->rowCount();
    }

    /**
     * Упрощенная операция: выборка одной записи
     */
    public function find(string $table, array $conditions, array $select = ['*']): ?array
    {
        $columns = implode(', ', $select);
        $whereClause = implode(' AND ', array_map(fn ($col) => "$col = :$col", array_keys($conditions)));

        $sql = sprintf(
            'SELECT %s FROM %s WHERE %s',
            $columns,
            $table,
            $whereClause
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Упрощенная операция: выборка всех записей
     */
    public function findAll(string $table, array $conditions = [], array $select = ['*']): array
    {
        $columns = implode(', ', $select);
        $sql = sprintf('SELECT %s FROM %s', $columns, $table);

        if (!empty($conditions)) {
            $whereClause = implode(' AND ', array_map(fn ($col) => "$col = :$col", array_keys($conditions)));
            $sql .= ' WHERE ' . $whereClause;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Упрощенная операция: выполнение произвольного запроса
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}

/**
 * Пример 4: Facade для работы с HTTP запросами
 */
class HttpClient
{
    private string $baseUrl;
    private array $defaultHeaders = [];
    private int $timeout = 30;

    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function setHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * GET запрос
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        $url = $this->buildUrl($endpoint, $query);
        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => $this->mergeHeaders($headers),
            ]
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
            'headers' => $this->parseHeaders($httpCode, $response),
        ];
    }

    private function buildUrl(string $endpoint, array $query = []): string
    {
        $url = $this->baseUrl ? $this->baseUrl . '/' . ltrim($endpoint, '/') : $endpoint;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private function mergeHeaders(array $additionalHeaders): array
    {
        return array_merge($this->defaultHeaders, $additionalHeaders);
    }

    private function parseHeaders(int $httpCode, string $response): array
    {
        // Простая парсинг заголовков (в реальности сложнее)
        return [
            'http_code' => $httpCode,
            'content_type' => 'application/json', // Упрощенно
        ];
    }

    /**
     * POST запрос
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->buildUrl($endpoint);
        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => $this->mergeHeaders($headers),
            ]
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
            'headers' => $this->parseHeaders($httpCode, $response),
        ];
    }

    /**
     * PUT запрос
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->buildUrl($endpoint);
        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => $this->mergeHeaders($headers),
            ]
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
            'headers' => $this->parseHeaders($httpCode, $response),
        ];
    }

    /**
     * DELETE запрос
     */
    public function delete(string $endpoint, array $query = [], array $headers = []): array
    {
        $url = $this->buildUrl($endpoint, $query);
        $ch = curl_init($url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => $this->mergeHeaders($headers),
            ]
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
            'headers' => $this->parseHeaders($httpCode, $response),
        ];
    }
}

/**
 * Пример 5: Facade для работы с платежами
 */
class PaymentFacade
{
    private PaymentGateway $gateway;
    private EmailSender $emailSender;
    private Logger $logger;

    public function __construct(
        PaymentGateway $gateway,
        EmailSender    $emailSender,
        Logger         $logger
    ) {
        $this->gateway = $gateway;
        $this->emailSender = $emailSender;
        $this->logger = $logger;
    }

    /**
     * Упрощенная операция: создание платежа
     */
    public function createPayment(
        string $orderId,
        float  $amount,
        string $currency,
        string $customerId,
        string $cardToken
    ): PaymentResult {
        $this->logger->log("Creating payment for order $orderId, amount: $amount $currency");

        try {
            // Создаем платеж через платежный шлюз
            $payment = $this->gateway->charge(
                $amount,
                $currency,
                $customerId,
                $cardToken
            );

            // Отправляем подтверждение
            $this->emailSender->send(
                $customerId . '@example.com',
                'Payment Confirmation',
                "Your payment of $amount $currency for order $orderId was successful!"
            );

            $this->logger->log("Payment $payment->id created successfully");

            return new PaymentResult(
                true,
                $payment->id,
                'Payment processed successfully'
            );

        } catch (PaymentException $e) {
            $this->logger->log('Payment failed: ' . $e->getMessage());

            return new PaymentResult(
                false,
                null,
                'Payment failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Упрощенная операция: возврат платежа
     */
    public function refundPayment(string $paymentId, float $amount): RefundResult
    {
        $this->logger->log("Processing refund for payment $paymentId");

        try {
            // Возвращаем платеж
            $refund = $this->gateway->refund($paymentId, $amount);

            // Отправляем уведомление
            $this->emailSender->send(
                $refund->customerEmail,
                'Refund Confirmation',
                "Refund of $amount has been processed for payment $paymentId"
            );

            $this->logger->log("Refund $refund->id processed successfully");

            return new RefundResult(
                true,
                $refund->id,
                'Refund processed successfully'
            );

        } catch (PaymentException $e) {
            $this->logger->log('Refund failed: ' . $e->getMessage());

            return new RefundResult(
                false,
                null,
                'Refund failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Упрощенная операция: проверка статуса платежа
     */
    public function checkPaymentStatus(string $paymentId): PaymentStatus
    {
        $this->logger->log("Checking status for payment $paymentId");

        $status = $this->gateway->getStatus($paymentId);

        return new PaymentStatus(
            $status->status,
            $status->amount,
            $status->currency,
            $status->createdAt
        );
    }
}


class PaymentResult
{

    private bool $success = false;
    private ?string $paymentId = null;
    private string $result = '';

    public function __construct(bool $success, ?string $paymentId, string $result)
    {
        $this->success = $success;
        $this->paymentId = $paymentId;
        $this->result = $result;
    }

    public function __get(string $name)
    {
        if ($name === 'success') {
            return (bool)$this->success;
        }

        if ($name === 'paymentId') {
            return $this->paymentId;// null|string
        }
        if ($name === 'result') {
            return (string)$this->result;
        }

        throw new RuntimeException("ERROR: access requested to unknown property \"PaymentResult::\${$name}\"");
    }

}

/**
 * Пример 6: Facade для работы с пользователями
 */
class UserManagementFacade
{
    private PDO $pdo;
    private EmailSender $emailSender;
    private Logger $logger;

    public function __construct(PDO $pdo, EmailSender $emailSender, Logger $logger)
    {
        $this->pdo = $pdo;
        $this->emailSender = $emailSender;
        $this->logger = $logger;
    }

    /**
     * Упрощенная операция: регистрация пользователя
     */
    public function registerUser(
        string $username,
        string $email,
        string $password,
        string $role = 'user'
    ): RegistrationResult {
        $this->logger->log("Registering user $username with email $email");

        // Валидация
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new RegistrationResult(false, null, 'Invalid email format');
        }

        // Проверка существования
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return new RegistrationResult(false, null, 'User already exists');
        }

        // Создание пользователя
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password, role, created_at) ' .
            'VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hashedPassword, $role, date('Y-m-d H:i:s')]);
        $userId = (int)$this->pdo->lastInsertId();

        // Отправка приветственного email
        $this->emailSender->send(
            $email,
            'Welcome!',
            "Hello $username,\n\nWelcome to our service! Your account has been created.\n\nBest regards,\nTeam"
        );

        $this->logger->log("User $username registered successfully");

        return new RegistrationResult(true, $userId, 'User registered successfully');
    }

    /**
     * Упрощенная операция: изменение роли пользователя
     */
    public function changeUserRole(int $userId, string $newRole): bool
    {
        $this->logger->log("Changing role for user $userId to $newRole");

        $allowedRoles = ['user', 'admin', 'moderator'];
        if (!in_array($newRole, $allowedRoles)) {
            throw new InvalidArgumentException("Invalid role: $newRole");
        }

        $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$newRole, $userId]);

        // Отправка уведомления
        $user = $this->getUserById($userId);
        if ($user) {
            $this->emailSender->send(
                $user['email'],
                'Role Changed',
                "Hello {$user['username']},\n\nYour role has been changed to $newRole.\n\nBest regards,\nTeam"
            );
        }

        $this->logger->log("Role changed for user $userId to $newRole");

        return true;
    }

    private function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT username, email FROM users WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Упрощенная операция: удаление пользователя
     */
    public function deleteUser(int $userId): bool
    {
        $this->logger->log("Deleting user $userId");

        // Получаем информацию о пользователе для уведомления
        $user = $this->getUserById($userId);

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);

        if ($user && $stmt->rowCount() > 0) {
            $this->emailSender->send(
                $user['email'],
                'Account Deleted',
                "Hello {$user['username']},\n\nYour account has been deleted.\n\nBest regards,\nTeam"
            );
        }

        $this->logger->log("User $userId deleted");

        return true;
    }
}

/**
 * Пример 7: Facade для работы с заказами
 */
class OrderFacade
{
    private PDO $pdo;
    private EmailSender $emailSender;
    private InventoryService $inventoryService;
    private PaymentGateway $paymentGateway;

    public function __construct(
        PDO              $pdo,
        EmailSender      $emailSender,
        InventoryService $inventoryService,
        PaymentGateway   $paymentGateway
    ) {
        $this->pdo = $pdo;
        $this->emailSender = $emailSender;
        $this->inventoryService = $inventoryService;
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Упрощенная операция: создание заказа
     */
    public function createOrder(int $userId, array $items): OrderResult
    {
        $this->logger()->log("Creating order for user $userId");

        // Проверяем наличие товаров
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            if (!$this->inventoryService->checkAvailability($productId, $quantity)) {
                return new OrderResult(false, null, "Product $productId is out of stock");
            }
        }

        // Резервируем товары
        foreach ($items as $item) {
            $this->inventoryService->reserveStock(
                $item['product_id'],
                $item['quantity']
            );
        }

        // Создаем заказ в базе
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO orders (user_id, status, total, created_at) ' .
                'VALUES (?, ?, ?, ?)'
            );
            $total = array_sum(array_column($items, 'price'));
            $stmt->execute([$userId, 'pending', $total, date('Y-m-d H:i:s')]);
            $orderId = (int)$this->pdo->lastInsertId();

            // Добавляем товары в заказ
            $stmt = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, price) ' .
                'VALUES (?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->logger()->log('Order creation failed: ' . $e->getMessage());

            // Отменяем резервирование
            foreach ($items as $item) {
                $this->inventoryService->releaseStock(
                    $item['product_id'],
                    $item['quantity']
                );
            }

            return new OrderResult(false, null, 'Failed to create order');
        }

        // Отправляем подтверждение
        $user = $this->getUserById($userId);
        if ($user) {
            $this->emailSender->send(
                $user['email'],
                'Order Confirmation',
                "Hello {$user['username']},\n\nYour order #$orderId has been placed successfully!\n\nBest regards,\nTeam"
            );
        }

        $this->logger()->log("Order $orderId created for user $userId");

        return new OrderResult(true, $orderId, 'Order created successfully');
    }

    private function logger(): Logger
    {
        // В реальном приложении это был бы dependency injection
        return new FileLogger('orders.log');
    }

    private function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Упрощенная операция: оплата заказа
     */
    public function payForOrder(int $orderId, string $cardToken): PaymentResult
    {
        $this->logger()->log("Processing payment for order $orderId");

        // Получаем информацию о заказе
        $order = $this->getOrder($orderId);
        if (!$order) {
            return new PaymentResult(false, null, 'Order not found');
        }

        if ($order['status'] !== 'pending') {
            return new PaymentResult(false, null, 'Order is not pending for payment');
        }

        // Обрабатываем платеж
        $paymentResult = $this->paymentGateway->charge(
            $order['total'],
            'USD',
            $order['user_id'],
            $cardToken
        );

        if (!$paymentResult->success) {
            return new PaymentResult(false, null, 'Payment failed: ' . $paymentResult->message);
        }

        // Обновляем статус заказа
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ?, payment_id = ? WHERE id = ?');
        $stmt->execute(['paid', $paymentResult->paymentId, $orderId]);

        // Отправляем подтверждение оплаты
        $user = $this->getUserById($order['user_id']);
        if ($user) {
            $this->emailSender->send(
                $user['email'],
                'Payment Confirmed',
                "Hello {$user['username']},\n\nYour payment for order #$orderId has been confirmed!\n\nBest regards,\nTeam"
            );
        }

        $this->logger()->log("Payment processed for order $orderId");

        return new PaymentResult(true, $paymentResult->paymentId, 'Payment processed successfully');
    }

    private function getOrder(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Упрощенная операция: отмена заказа
     */
    public function cancelOrder(int $orderId): bool
    {
        $this->logger()->log("Cancelling order $orderId");

        // Получаем информацию о заказе
        $order = $this->getOrder($orderId);
        if (!$order) {
            throw new InvalidArgumentException('Order not found');
        }

        if ($order['status'] === 'cancelled') {
            return true; // Уже отменен
        }

        // Отменяем резервирование товаров
        $items = $this->getOrderItems($orderId);
        foreach ($items as $item) {
            $this->inventoryService->releaseStock(
                $item['product_id'],
                $item['quantity']
            );
        }

        // Возвращаем платеж если нужно
        if ($order['status'] === 'paid' && !empty($order['payment_id'])) {
            try {
                $this->paymentGateway->refund($order['payment_id'], $order['total']);
            } catch (Exception $e) {
                $this->logger()->log("Refund failed for order $orderId: " . $e->getMessage());
            }
        }

        // Обновляем статус заказа
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['cancelled', $orderId]);

        // Отправляем уведомление
        $user = $this->getUserById($order['user_id']);
        if ($user) {
            $this->emailSender->send(
                $user['email'],
                'Order Cancelled',
                "Hello {$user['username']},\n\nYour order #$orderId has been cancelled.\n\nBest regards,\nTeam"
            );
        }

        $this->logger()->log("Order $orderId cancelled");

        return true;
    }

    private function getOrderItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $stmt->execute([$orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Facade — предоставляет упрощенный интерфейс к сложной подсистеме
 *    Субсистема — содержит сложную логику, которую скрывает Facade
 *    Клиент — использует Facade вместо прямого взаимодействия с субсистемой
 *
 * Как это работает:
 *    Facade предоставляет упрощенный интерфейс к сложной подсистеме
 *    Клиент работает только с Facade, не зная о внутренней сложности
 *    Подсистема содержит всю сложную логику, но клиент о ней не знает
 *
 * Преимущества:
 *    Упрощение интерфейса — клиенту не нужно знать внутреннюю структуру
 *    Разделение ответственности — Facade управляет сложностью, клиент фокусируется на бизнес-логике
 *    Уменьшение связанности — клиент не зависит от изменений в подсистеме
 *    Улучшение поддержки — изменения в подсистеме не затрагивают клиентский код
 *    Безопасность — клиент не может случайно сломать внутреннюю логику
 * Недостатки:
 *    Утечка абстракции — если Facade становится слишком сложным, он сам становится проблемой
 *    Производительность — дополнительный уровень косвенности
 *    Дублирование кода — если Facade не охватывает все сценарии, может потребоваться дополнительный код
 *    Сложность отладки — сложнее отследить, что происходит внутри Facade
 *
 * Где используется в фреймворках:
 * 1. Laravel's Facades
 *    // Laravel Facades - статический доступ к сервисам контейнера
 *    Route::get('/', function () {
 *        return View::make('welcome');
 *    });
 *    // Или
 *    Cache::put('key', 'value', 60);
 *    $value = Cache::get('key');
 *    // Пример с DB facade
 *    $users = DB::table('users')->get();
 * 2. Laravel's Artisan Commands
 *    // Artisan команды как Facade для сложных операций
 *    Artisan::command('inspire', function () {
 *        $this->comment(Inspiring::quote());
 *    });
 * 3. Laravel's Mail Facade
 *    // Mail facade для отправки email
 *    Mail::to($request->user())
 *        ->send(new OrderShipped($order));
 * 4. Laravel's Notification Facade
 *    // Notification facade
 *    Notification::send($user, new WelcomeNotification());
 * 5. Laravel's Storage Facade
 *    // Storage facade для работы с файлами
 *    Storage::disk('local')
 *        ->put('file.txt', 'Contents');
 * 6. Laravel's Config Facade
 *    // Config facade
 *    $value = Config::get('app.timezone');
 * 7. Laravel's Event Facade
 *    // Event facade
 *    Event::dispatch(new OrderShipped($order));
 * 8. Laravel's Validator Facade
 *    // Validator facade
 *    $validator = Validator::make($request->all(), [
 *        'title' => 'required|string|max:255',
 *    ]);
 * 9. Laravel's Session Facade
 *    // Session facade
 *    Session::put('key', 'value');
 *    $value = Session::get('key');
 * 10. Laravel's Cookie Facade
 *    // Cookie facade
 *    Cookie::queue('name', 'value', $minutes);
 * 11. Laravel's Auth Facade
 *    // Auth facade
 *    $user = Auth::user();
 *    if (Auth::check()) {
 *        // Пользователь аутентифицирован
 *    }
 * 12. Laravel's Request Facade
 *    // Request facade
 *    $name = Request::input('name');
 *    $path = Request::path();
 * 13. Symfony's Request Stack
 *    // Symfony RequestStack как Facade для текущего запроса
 *    $request = $this->get('request_stack')->getCurrentRequest();
 *    $method = $request->getMethod();
 * 14. Symfony's Router
 *    // Router как Facade для генерации URL
 *    $url = $this->get('router')->generate('homepage');
 * 15. Zend Framework's Service Locator
 *    // Service Locator как Facade для сервисов
 *    $service = $this->getServiceLocator()->get('MyService');
 * 16. Yii Framework's Yii::$app
 *    // Yii::$app как глобальный Facade
 *    $request = Yii::$app->request;
 *    $response = Yii::$app->response;
 * 17. CakePHP's ClassRegistry
 *    // ClassRegistry как Facade для компонентов
 *    $component = ClassRegistry::init('MyComponent');
 * 18. Nette's Context
 *    // Nette Context как Facade
 *    $service = $this->context->getByType('MyService');
 * 19. Doctrine's EntityManager
 *    // EntityManager как Facade для работы с ORM
 *    $entityManager = EntityManager::create($dbParams, $config);
 *    $user = $entityManager->find('User', $id);
 * 20. Laravel's Socialite
 *    // Socialite как Facade для OAuth
 *    return Socialite::driver('github')->redirect();
 *
 * Когда полезен:
 *    Сложные системы — когда нужно упростить взаимодействие с библиотекой или фреймворком
 *    Библиотеки сторонних разработчиков — когда нужно предоставить простой интерфейс к сложной библиотеке
 *    Поддержка кода — когда нужно изолировать изменения в сложной системе
 *    Документация — когда нужно предоставить понятный API для клиентов
 *    Тестирование — когда нужно мокировать(имитировтаь/подменять) сложную систему
 *
 */


try {

    echo "=== Facade Pattern Example ===\n\n";

    // Создаем сложную подсистему
    $subsystem = new ComplexSubsystem();

    // Создаем фасад
    $facade = new SystemFacade($subsystem);

    // Используем фасад вместо прямого взаимодействия с подсистемой
    echo "Processing order #123...\n";
    $result = $facade->processOrder('123', ['item1', 'item2']);
    echo 'Result: ' . json_encode($result) . "\n\n";

    echo "Generating report...\n";
    $report = $facade->generateReport('sales', '2024-Q1');
    echo 'Report: ' . json_encode($report) . "\n\n";


    echo "=== File Manager Example ===\n\n";
    $fileManager = new FileManager('/tmp/demo');

    // Упрощенные операции с файлами
    $fileManager->createFile('test.txt', 'Hello, World!');
    echo "File created\n";

    $content = $fileManager->readFile('test.txt');
    echo "File content: $content\n";

    $fileManager->copyFile('test.txt', 'test_copy.txt');
    echo "File copied\n";

    $fileManager->renameFile('test_copy.txt', 'renamed.txt');
    echo "File renamed\n";

    $files = $fileManager->listFiles();
    echo 'Files in directory: ' . implode(', ', $files) . "\n";

    $fileManager->deleteFile('renamed.txt');
    echo "File deleted\n";


    echo "\n=== Database Facade Example ===\n\n";

    // Создаем базу данных
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

    $db = new DatabaseFacade($pdo);

    // Упрощенные операции с базой данных
    $userId = $db->insert('users', ['name' => 'John Doe', 'email' => 'john@example.com']);
    echo "User inserted with ID: $userId\n";

    $user = $db->find('users', ['id' => $userId]);
    echo 'Found user: ' . json_encode($user) . "\n";

    $db->update('users', ['name' => 'John Smith'], ['id' => $userId]);
    echo "User updated\n";

    $users = $db->findAll('users');
    echo 'All users: ' . json_encode($users) . "\n";

    $db->delete('users', ['id' => $userId]);
    echo "User deleted\n";


    echo "\n=== HTTP Client Example ===\n\n";
    // Free fake and reliable API for testing and prototyping.
    $client = new HttpClient('https://jsonplaceholder.typicode.com');

    // Упрощенные HTTP запросы
    $response = $client->get('/posts/1');
    echo 'GET response status: ' . $response['status'] . "\n";
    echo 'GET response body: ' . json_encode(json_decode($response['body'], true)) . "\n";

    $response = $client->post('/posts', [
        'title' => 'Test Post',
        'body' => 'This is a test post',
        'userId' => 1,
    ]);
    echo 'POST response status: ' . $response['status'] . "\n";


    echo "\n=== Payment Facade Example ===\n\n";

    // Создаем зависимости
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)');
    $pdo->exec('INSERT INTO users (email) VALUES ("customer@example.com")');

    $emailSender = new class implements EmailSender {
        public function send(string $to, string $subject, string $message): bool
        {
            echo "Email sent to $to with subject '$subject'\n";
            return true;
        }
    };

    $logger = new class implements Logger {
        public function log(string $message): void
        {
            echo 'Log: ' . $message . "\n";
        }
    };

    $paymentGateway = new class implements PaymentGateway {
        public function charge(float $amount, string $currency, string $customerId, string $cardToken): object
        {
            return (object)[
                'id' => 'payment_' . uniqid(),
                'amount' => $amount,
                'currency' => $currency,
                'customerId' => $customerId,
            ];
        }

        public function refund(string $paymentId, float $amount): object
        {
            return (object)[
                'id' => 'refund_' . uniqid(),
                'paymentId' => $paymentId,
                'amount' => $amount,
            ];
        }

        public function getStatus(string $paymentId): object
        {
            return (object)[
                'status' => 'completed',
                'amount' => 100.00,
                'currency' => 'USD',
                'createdAt' => date('Y-m-d H:i:s'),
            ];
        }
    };

    $paymentFacade = new PaymentFacade($paymentGateway, $emailSender, $logger);

    $result = $paymentFacade->createPayment('order_123', 99.99, 'USD', 'customer1', 'card_token_123');
    echo 'Payment result: ' . ($result->success ? 'Success' : 'Failed') . "\n";
    echo 'Payment ID: ' . ($result->paymentId ?? 'N/A') . "\n";

    echo "\n=== Order Facade Example ===\n\n";

    // Создаем зависимости
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT)');
    $pdo->exec('CREATE TABLE orders (id INTEGER PRIMARY KEY, user_id INTEGER, status TEXT, total DECIMAL(10,2), created_at TEXT, payment_id TEXT)');
    $pdo->exec('CREATE TABLE order_items (id INTEGER PRIMARY KEY, order_id INTEGER, product_id INTEGER, quantity INTEGER, price DECIMAL(10,2))');
    $pdo->exec('INSERT INTO users (username, email) VALUES ("john_doe", "john@example.com")');

    $emailSender = new class implements EmailSender {
        public function send(string $to, string $subject, string $message): bool
        {
            echo "Email sent to $to with subject '$subject'\n";
            return true;
        }
    };

    $inventoryService = new class implements InventoryService {
        private array $inventory = [
            1 => ['quantity' => 10, 'price' => 10.00],
            2 => ['quantity' => 5, 'price' => 20.00],
        ];

        public function checkAvailability(int $productId, int $quantity): bool
        {
            return ($this->inventory[$productId]['quantity'] ?? 0) >= $quantity;
        }

        public function reserveStock(int $productId, int $quantity): bool
        {
            if ($this->checkAvailability($productId, $quantity)) {
                $this->inventory[$productId]['quantity'] -= $quantity;
                return true;
            }
            return false;
        }

        public function releaseStock(int $productId, int $quantity): bool
        {
            $this->inventory[$productId]['quantity'] += $quantity;
            return true;
        }
    };

    $paymentGateway = new class implements PaymentGateway {
        public function charge(float $amount, string $currency, string $customerId, string $cardToken): object
        {
            return (object)[
                'id' => 'payment_' . uniqid(),
                'amount' => $amount,
                'currency' => $currency,
                'customerId' => $customerId,
            ];
        }

        public function refund(string $paymentId, float $amount): object
        {
            return (object)[
                'id' => 'refund_' . uniqid(),
                'paymentId' => $paymentId,
                'amount' => $amount,
            ];
        }

        public function getStatus(string $paymentId): object
        {
            return (object)[
                'status' => 'completed',
                'amount' => 100.00,
                'currency' => 'USD',
                'createdAt' => date('Y-m-d H:i:s'),
            ];
        }
    };

    $orderFacade = new OrderFacade($pdo, $emailSender, $inventoryService, $paymentGateway);

    $items = [
        ['product_id' => 1, 'quantity' => 2, 'price' => 10.00],
        ['product_id' => 2, 'quantity' => 1, 'price' => 20.00],
    ];

    $orderResult = $orderFacade->createOrder(1, $items);
    echo 'Order created: ' . ($orderResult->success ? 'Success' : 'Failed') . "\n";
    echo 'Order ID: ' . ($orderResult->orderId ?? 'N/A') . "\n";

    if ($orderResult->success) {
        /** @var PaymentResult $paymentResult */
        $paymentResult = $orderFacade->payForOrder($orderResult->orderId, 'card_token_123');
        echo 'Payment: ' . ($paymentResult->success ? 'Success' : 'Failed') . "\n";
        echo 'Payment ID: ' . ($paymentResult->paymentId ?? 'N/A') . "\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

/* Для PHP 8.4 можно использовать:   ******

// Использование атрибутов для автоматического создания Facade
#[Facade]
class OrderService
{
    public function createOrder(array $data): Order
    {
        // Сложная логика
    }
}

// Генерация Facade через атрибуты
class FacadeBuilder
{
    public function build(string $facadeClass): object
    {
        $reflector = new ReflectionClass($facadeClass);
        $instance = $reflector->newInstance();

        // Автоматическое внедрение зависимостей
        foreach ($reflector->getProperties() as $property) {
            $attributes = $property->getAttributes(Inject::class);
            foreach ($attributes as $attribute) {
                // @var Inject $inject
                $inject = $attribute->newInstance();
                $property->setValue($instance, $this->get($inject->value));
            }
        }

        return $instance;
    }
}

// Pattern matching для автоматического создания Facade
public function getFacade(string $type): object
{
    return match ($type) {
        'order' => new OrderFacade($pdo, $emailSender, $inventoryService, $paymentGateway),
        'payment' => new PaymentFacade($paymentGateway, $emailSender, $logger),
        'user' => new UserManagementFacade($pdo, $emailSender, $logger),
        'system' => new SystemFacade(new ComplexSubsystem()),
        default => throw new InvalidArgumentException("Unknown facade type: $type"),
    };
}

// Enum для типов Facade
enum FacadeType: string
{
    case ORDER = 'order';
    case PAYMENT = 'payment';
    case USER = 'user';
    case SYSTEM = 'system';
    case FILE = 'file';
    case DATABASE = 'database';
    case HTTP = 'http';
}

class FacadeFactory
{
    public function create(FacadeType $type, array $dependencies = []): object
    {
        return match ($type) {
            FacadeType::ORDER => new OrderFacade(
                $dependencies['pdo'],
                $dependencies['emailSender'],
                $dependencies['inventoryService'],
                $dependencies['paymentGateway']
            ),
            FacadeType::PAYMENT => new PaymentFacade(
                $dependencies['paymentGateway'],
                $dependencies['emailSender'],
                $dependencies['logger']
            ),
            FacadeType::USER => new UserManagementFacade(
                $dependencies['pdo'],
                $dependencies['emailSender'],
                $dependencies['logger']
            ),
            FacadeType::SYSTEM => new SystemFacade(new ComplexSubsystem()),
            FacadeType::FILE => new FileManager($dependencies['basePath']),
            FacadeType::DATABASE => new DatabaseFacade($dependencies['pdo']),
            FacadeType::HTTP => new HttpClient($dependencies['baseUrl']),
        };
    }
}

************************************ */

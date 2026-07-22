<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 08.07.2026 - 13:18
 */

namespace Structural\Proxy;

/**
 * Subject Interface - общий интерфейс для RealSubject и Proxy
 */
interface ImageInterface
{
    public function display(): string;
    public function getProperties(): array;
}

/**
 * Real Subject - реальный объект
 */
class RealImage implements ImageInterface
{
    private string $filename;
    private ?string $imageData = null;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->loadFromDisk();
    }

    private function loadFromDisk(): void
    {
        // Имитация загрузки большого файла изображения
        sleep(2); // Симуляция задержки
        $this->imageData = "Image data from {$this->filename}";

        echo "Loaded image: {$this->filename}\n";
    }

    public function display(): string
    {
        return $this->imageData ?? '';
    }

    public function getProperties(): array
    {
        return [
            'filename' => $this->filename,
            'size' => strlen($this->imageData ?? ''),
            'format' => pathinfo($this->filename, PATHINFO_EXTENSION)
        ];
    }
}

/**
 * Пример 1: Proxy - заместитель
 */
class ImageProxy implements ImageInterface
{
    private RealImage $realImage;
    private string $filename;
    private ?string $thumbCache = null;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function display(): string
    {
        // Lazy loading - создаем RealImage только когда нужно
        if (!isset($this->realImage)) {
            $this->realImage = new RealImage($this->filename);
        }

        return $this->realImage->display();
    }

    public function getProperties(): array
    {
        // Если RealImage уже загружен, возвращаем его свойства
        if (isset($this->realImage)) {
            return $this->realImage->getProperties();
        }

        // Иначе возвращаем информацию о файле
        return [
            'filename' => $this->filename,
            'size' => filesize($this->filename) ?: 0,
            'format' => pathinfo($this->filename, PATHINFO_EXTENSION),
            'status' => 'not loaded'
        ];
    }

    // Дополнительный метод для получения превью
    public function getThumbnail(int $width = 100, int $height = 100): string
    {
        if ($this->thumbCache === null) {
            // Имитация создания превью
            $this->thumbCache = "Thumbnail of {$this->filename} ({$width}x{$height})";
        }

        return $this->thumbCache;
    }

    // Дополнительный метод для проверки доступности
    public function isLoaded(): bool
    {
        return isset($this->realImage);
    }
}


/**
 * Пример 2: Virtual Proxy для тяжелых объектов
 */
class DatabaseConnection
{
    private string $host;
    private int $port;
    private bool $connected = false;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function connect(): void
    {
        // Имитация подключения к БД
        sleep(1);
        $this->connected = true;

        echo "Connected to database {$this->host}:{$this->port}\n";
    }

    public function query(string $sql): array
    {
        if (!$this->connected) {
            throw new \RuntimeException('Not connected to database');
        }

        // Имитация выполнения запроса

        return ['result' => $sql];
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }
}

class DatabaseConnectionProxy
{
    private DatabaseConnection $connection;
    private string $host;
    private int $port;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function connect(): void
    {
        if (!isset($this->connection)) {
            $this->connection = new DatabaseConnection($this->host, $this->port);
            $this->connection->connect();
        }
    }

    public function query(string $sql): array
    {
        $this->connect(); // Гарантируем подключение
        return $this->connection->query($sql);
    }

    public function isConnected(): bool
    {
        return isset($this->connection) && $this->connection->isConnected();
    }

    /**
     * Доп-й метод для управления транзакциями
     */
    public function beginTransaction(): void
    {
        $this->connect();

        echo "Transaction started\n";
    }

    public function commit(): void
    {
        if ($this->isConnected()) {
            echo "Transaction committed\n";
        }
    }
}


/**
 * Пример 3: Protection Proxy (защитный прокси)
 */
interface UserInterface
{
    public function getProfile(): array;
    public function updateProfile(array $data): bool;
    public function deleteAccount(): bool;
}

class User implements UserInterface
{
    private string $username;
    private string $email;
    private bool $isAdmin;

    public function __construct(string $username, string $email, bool $isAdmin = false)
    {
        $this->username = $username;
        $this->email = $email;
        $this->isAdmin = $isAdmin;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function isAdmin(): bool
    {
        return (bool) $this->isAdmin ?? false;
    }

    public function getProfile(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->isAdmin ? 'admin' : 'user'
        ];
    }

    public function updateProfile(array $data): bool
    {
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        return true;
    }

    public function deleteAccount(): bool
    {
        // Логика удаления аккаунта
        echo "Account {$this->username} deleted\n";

        return true;
    }

}

class UserProxy implements UserInterface
{
    private User $user;
    private ?User $currentUser;

    public function __construct(User $user, ?User $currentUser = null)
    {
        $this->user = $user;
        $this->currentUser = $currentUser;
    }

    public function getProfile(): array
    {
        // Все могут просматривать свой профиль
        if ($this->currentUser && $this->currentUser->getUsername() === $this->user->getUsername()) {

            return $this->user->getProfile();
        }

        // Только админы могут просматривать чужие профили
        if ($this->currentUser && $this->currentUser->isAdmin()) {
            return $this->user->getProfile();
        }

        throw new \RuntimeException('Access denied');
    }

    public function updateProfile(array $data): bool
    {
        // Только владелец может обновлять свой профиль
        if ($this->currentUser && $this->currentUser->getUsername() === $this->user->getUsername()) {
            return $this->user->updateProfile($data);
        }

        throw new \RuntimeException('Access denied');
    }

    public function deleteAccount(): bool
    {
        // Только админы могут удалять аккаунты
        if ($this->currentUser && $this->currentUser->isAdmin()) {
            return $this->user->deleteAccount();
        }

        throw new \RuntimeException('Access denied');
    }

    // Дополнительный метод для проверки прав
    public function canEdit(): bool
    {
        if (!$this->currentUser) {
            return false;
        }

        return $this->currentUser->getUsername() === $this->user->getUsername()
            || $this->currentUser->isAdmin();
    }
}


/**
 * Пример 4: Logging Proxy
 */
class ApiClient
{
    public function get(string $url): array
    {
        // Имитация API запроса
        return ['data' => 'some content from ' . $url];
    }

    public function post(string $url, array $data): array
    {
        // Имитация POST запроса
        return ['status' => 'created', 'data' => $data];
    }
}

class ApiClientProxy
{
    private ApiClient $client;
    private array $requestLog = [];
    private array $responseLog = [];

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function get(string $url): array
    {
        $startTime = microtime(true);

        $result = $this->client->get($url);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $this->logRequest('GET', $url, $duration, $result);

        return $result;
    }

    public function post(string $url, array $data): array
    {
        $startTime = microtime(true);

        $result = $this->client->post($url, $data);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $this->logRequest('POST', $url, $duration, $result, $data);

        return $result;
    }

    private function logRequest(string $method, string $url, float $duration, array $response, ?array $data = null): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $method,
            'url' => $url,
            'duration' => round($duration * 1000, 2) . 'ms',
            'response' => $response
        ];

        if ($data !== null) {
            $logEntry['request_data'] = $data;
        }

        $this->requestLog[] = $logEntry;

        // Сохраняем только последние 100 записей
        if (count($this->requestLog) > 100) {
            array_shift($this->requestLog);
        }
    }

    public function getRequestLog(): array
    {
        return $this->requestLog;
    }

    public function getLastRequest(): ?array
    {
        return $this->requestLog ? end($this->requestLog) : null;
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Subject Interface - общий интерфейс для RealSubject и Proxy
 *    Real Subject - реальный объект, который выполняет работу
 *    Proxy - заместитель, который контролирует доступ к RealSubject
 *    Client - работает с Subject интерфейсом, не зная о существовании Proxy
 *
 * Преимущества:
 *    Ленивая загрузка - объект создается только когда действительно нужен
 *    Контроль доступа - проверка прав перед доступом к объекту
 *    Логирование - отслеживание всех операций с объектом
 *    Кэширование - хранение результатов операций
 *    Виртуальные прокси - создание тяжелых объектов по требованию
 *    Защитные прокси - проверка прав доступа
 *    Умные ссылки - дополнительные действия при обращении к объекту
 * Недостатки:
 *    Сложность - добавляет дополнительный уровень косвенности
 *    Производительность - может влиять на производительность
 *    Избыточность - если объект всегда используется, прокси излишний
 *    Дизайн - может усложнить архитектуру
 *
 * Где используется (в попсовых фреймворках):
 * 1. Laravel's Eloquent Lazy Loading
 *    $user = User::find(1);
 *    // Отношения загружаются только когда обращаемся к ним
 *    foreach ($user->posts as $post) {
 *        // Загрузка posts происходит здесь (незабываем N+1 на Lazy)
 *    }
 * 2. Laravel's Gate & Policy Proxies
 *    // Прокси для проверки прав доступа
 *    if (Gate::allows('update-post', $post)) {
 *        // Проверяется политика через прокси
 *    }
 *    // Или
 *    $this->authorize('update-post', $post);
 * 3. Laravel's Event Proxies
 *    // Прокси для событий
 *    event(new OrderShipped($order));
 *    // За кулисами: EventServiceProvider->dispatch()
 * 4. Laravel's Queue Proxies
 *    // Прокси для отложенных задач
 *    ProcessPodcast::dispatch($podcast)
 *        ->onQueue('processing')
 *        ->delay(60);
 *    // За кулисами: QueueManager->connection()->later()
 * 5. Laravel's Notification Proxies
 *    // Прокси для уведомлений
 *    $user->notify(new InvoicePaid($invoice));
 *    // За кулисами: NotificationSender->send()
 * 6. Laravel's Cache Proxies
 *    // Прокси для кэширования
 *    $value = Cache::remember('key', 60, function () {
 *        return db()->getExpensiveData();
 *    });
 *    // За кулисами: Repository->remember()
 * 7. Laravel's Filesystem Proxies
 *    // Прокси для файловой системы
 *    Storage::disk('s3')->put('file.jpg', $contents);
 *    // За кулисами: FilesystemAdapter->put()
 * 8. Laravel's HTTP Client Proxies
 *    // Прокси для HTTP запросов
 *    $response = Http::withHeaders(['X-Custom' => 'value'])
 *        ->timeout(5)
 *        ->get('https://api.example.com/data');
 *    // За кулисами: Client->get()
 * 9. Symfony's HttpKernel Proxy
 *    // Прокси для обработки запросов
 *    $response = $kernel->handle($request);
 *    // За кулисами: ControllerResolver->getController()
 * 10. Laravel's Service Container Proxies
 *    // Прокси для сервисов
 *    app('App\Services\PaymentGateway');
 *    // За кулисами: Container->make()
 *
 * Когда полезен:
 *    Тяжелые объекты - создание по требованию (lazy loading), БД?? проверь N+1 !!
 *    Контроль доступа - проверка прав перед доступом
 *    Логирование - отслеживание всех операций
 *    Кэширование - хранение результатов операций
 *    Виртуальные ресурсы - файлы, сетевые соединения
 *    Безопасность - защита от несанкционированного доступа
 *    Мониторинг - отслеживание использования объектов
 */



echo "=== Image Proxy Example ===\n";

// Создаем прокси для изображения
$imageProxy = new ImageProxy('large_image.jpg');

// Сначала получаем только метаданные (изображение еще не загружено)
echo 'Properties before display: ' . json_encode($imageProxy->getProperties()) . "\n";
//{"filename":"large_image.jpg","size":0,"format":"jpg","status":"not loaded"}

// Теперь отображаем изображение (оно загрузится только сейчас)
echo "Displaying image...\n" . $imageProxy->display() . "\n";
//Image data from large_image.jpg

// Повторный вызов - изображение уже загружено, не нужно загружать заново
echo "Displaying again...\n" . $imageProxy->display() . "\n";
//Image data from large_image.jpg

echo 'Properties after display: ' . json_encode($imageProxy->getProperties()) . "\n";
//{"filename":"large_image.jpg","size":31,"format":"jpg"}

// Получаем превью (не загружая все изображение)
echo 'Thumbnail: ' . $imageProxy->getThumbnail() . "\n";
//Thumbnail of large_image.jpg (100x100)



echo "\n=== Database Connection Proxy Example ===\n";

$dbProxy = new DatabaseConnectionProxy('localhost', 3306);

// Подключаемся только когда выполняем первый запрос
echo "Querying database...\n";
$result = $dbProxy->query('SELECT * FROM users');
echo 'Result: ' . json_encode($result) . "\n";
//{"result":"SELECT * FROM users"}

// Повторный запрос - подключение уже установлено
echo "Querying again...\n";
$result = $dbProxy->query('SELECT * FROM posts');
echo 'Result: ' . json_encode($result) . "\n";
//{"result":"SELECT * FROM posts"}

echo 'Is connected? ' . ($dbProxy->isConnected() ? 'YES' : 'NO') . "\n";//YES

// Используем транзакции
$dbProxy->beginTransaction();
$dbProxy->commit();
//Transaction started
//Transaction committed



echo "\n=== Protection Proxy Example ===\n";

$john = new User('john_doe', 'john@example.com');
$admin = new User('admin_user', 'admin@example.com', true);

// John просматривает свой профиль
$johnProxy = new UserProxy($john, $john);
echo "John's profile: " . json_encode($johnProxy->getProfile()) . "\n";
//{"username":"john_doe","email":"john@example.com","role":"user"}

// John пытается обновить свой профиль(email)
$johnProxy->updateProfile(['email' => 'john.new@example.com']);
echo 'Updated profile: ' . json_encode($johnProxy->getProfile()) . "\n";
//{"username":"john_doe","email":"john.new@example.com","role":"user"}

// Admin просматривает профиль John
$adminProxy = new UserProxy($john, $admin);
echo "Admin viewing John's profile: " . json_encode($adminProxy->getProfile()) . "\n";
//{"username":"john_doe","email":"john.new@example.com","role":"user"}

// Admin удаляет аккаунт John
$adminProxy->deleteAccount();
//Account john_doe deleted

// John пытается удалить свой аккаунт (не админ)
try {
    $johnProxy->deleteAccount();
} catch (\RuntimeException $e) {
    echo 'John delete attempt failed: ' . $e->getMessage() . "\n";
}
//John delete attempt failed: Access denied



echo "\n=== Logging Proxy Example ===\n";

$apiClient = new ApiClient();
$apiProxy = new ApiClientProxy($apiClient);

// Выполняем несколько запросов
$apiProxy->get('https://api.example.com/users');
$apiProxy->post('https://api.example.com/users', ['name' => 'John', 'email' => 'john@example.com']);
$apiProxy->get('https://api.example.com/posts');

// Проверяем логи (timestamp - timestamp текущая дата+время)
echo "Request log:\n";
foreach ($apiProxy->getRequestLog() as $log) {
    echo " - {$log['timestamp']} {$log['method']} {$log['url']} ({$log['duration']}): "
        . json_encode($log['response']) . "\n";
}
// - 2026-07-08 15:15:51 GET https://api.example.com/users (0ms): {"data":"some content from https:\/\/api.example.com\/users"}
// - 2026-07-08 15:15:51 POST https://api.example.com/users (0ms): {"status":"created","data":{"name":"John","email":"john@example.com"}}
// - 2026-07-08 15:15:51 GET https://api.example.com/posts (0ms): {"data":"some content from https:\/\/api.example.com\/posts"}

// Проверяем последний запрос
$last = $apiProxy->getLastRequest();
echo "\nLast request: " . json_encode($last) . "\n";
//{"timestamp":"2026-07-08 15:15:51","method":"GET","url":"https:\/\/api.example.com\/posts","duration":"0ms","response":{"data":"some content from https:\/\/api.example.com\/posts"}}



/* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// php8.4 стиля Использование атрибутов для определения прокси
#[Proxy(target: RealImage::class)]
class ImageProxy implements ImageInterface {}

// Генерация прокси через атрибуты
class ProxyBuilder
{
    public function build(string $subjectClass, string $realSubjectClass): object
    {
        $reflector = new ReflectionClass($realSubjectClass);
        $methods = $reflector->getMethods();

        $proxyCode = "class GeneratedProxy implements {$subjectClass} {\n";
        $proxyCode .= "    protected \${$realSubjectClass};\n\n";
        $proxyCode .= "    public function __construct() {\n";
        $proxyCode .= "        // Lazy initialization\n";
        $proxyCode .= "    }\n\n";

        foreach ($methods as $method) {
            if (!$method->isConstructor() && !$method->isStatic()) {
                $params = $method->getParameters();
                $proxyCode .= "    public function " . $method->getName() . "(";
                $proxyCode .= implode(', ', array_map(
                    fn($param) => $param->getType() . ' $' . $param->getName(),
                    $params
                ));
                $proxyCode .= "): " . ($method->hasReturnType() ? $method->getReturnType() : 'void') . " {\n";
                $proxyCode .= "        // Pre-processing\n";
                $proxyCode .= "        if (!isset(\$this->{$realSubjectClass})) {\n";
                $proxyCode .= "            \$this->{$realSubjectClass} = new {$realSubjectClass}();\n";
                $proxyCode .= "        }\n";
                $proxyCode .= "        // Method call\n";
                $proxyCode .= "        return \$this->{$realSubjectClass}->" . $method->getName() . "(";
                $proxyCode .= implode(', ', array_map(fn($param) => '$' . $param->getName(), $params));
                $proxyCode .= ");\n";
                $proxyCode .= "        // Post-processing\n";
                $proxyCode .= "    }\n\n";
            }
        }

        $proxyCode .= "}\n";

        eval($proxyCode);
        return new GeneratedProxy();
    }
}

// Pattern matching для определения типа прокси
public function getProxy(string $type): ImageInterface
{
    return match ($type) {
        'image' => new ImageProxy('large_image.jpg'),
        'database' => new DatabaseConnectionProxy('localhost', 3306),
        default => throw new InvalidArgumentException("Unknown proxy type: $type"),
    };
}

// Enum для типов прокси
enum ProxyType: string
{
    case IMAGE = 'image';
    case DATABASE = 'database';
    case USER = 'user';
    case API = 'api';
}

class ProxyFactory
{
    public function create(ProxyType $type, array $params = []): object
    {
        return match ($type) {
            ProxyType::IMAGE => new ImageProxy($params['filename']),
            ProxyType::DATABASE => new DatabaseConnectionProxy($params['host'], $params['port']),
            ProxyType::USER => new UserProxy($params['user'], $params['currentUser']),
            ProxyType::API => new ApiClientProxy(new ApiClient()),
        };
    }
}
 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

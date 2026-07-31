<?php

declare(strict_types=1);

namespace Structural\DependencyInjection;

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Interface EmailSender - интерфейс для отправки email
 */
interface EmailSender
{
    public function send(string $to, string $subject, string $message): bool;
}

/**
 * Interface SmsSender - интерфейс для отправки SMS
 */
interface SmsSender
{
    public function send(string $to, string $message): bool;
}

/**
 * Interface Logger - интерфейс для логирования
 */
interface Logger
{
    public function log(string $message): void;
}

/**
 * Concrete Implementation - SMTP отправка
 */
class SmtpEmailSender implements EmailSender
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;

    public function __construct(string $host, int $port, string $username, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function send(string $to, string $subject, string $message): bool
    {
        // Имитация отправки через SMTP
        echo "SMTP: Sending email to $to with subject '$subject'\n";
        return true;
    }
}

/**
 * Concrete Implementation - SendGrid API
 */
class SendGridEmailSender implements EmailSender
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function send(string $to, string $subject, string $message): bool
    {
        // Имитация отправки через SendGrid API
        echo "SendGrid: Sending email to $to with subject '$subject'\n";
        return true;
    }
}

/**
 * Concrete Implementation - Mock для тестирования
 */
class MockEmailSender implements EmailSender
{
    private array $sentEmails = [];

    public function send(string $to, string $subject, string $message): bool
    {
        $this->sentEmails[] = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
        ];
        return true;
    }

    public function getSentEmails(): array
    {
        return $this->sentEmails;
    }
}

/**
 * Service Class - регистрация пользователя
 */
class UserService
{
    private EmailSender $emailSender;
    private PDO $pdo;

    /**
     * Constructor Injection - зависимости передаются через конструктор
     */
    public function __construct(EmailSender $emailSender, PDO $pdo)
    {
        $this->emailSender = $emailSender;
        $this->pdo = $pdo;
    }

    public function register(string $username, string $email, string $password): bool
    {
        // Валидация
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Сохраняем в базу данных
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt->execute([$username, $email, $hashedPassword]);

        // Отправляем приветственное письмо
        $this->emailSender->send(
            $email,
            'Welcome!',
            "Hello $username,\n\nWelcome to our service!\n\nBest regards,\nTeam"
        );

        return true;
    }
}

/**
 * Service Class - получение пользователя
 */
class UserProfileService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateProfile(int $id, string $bio): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET bio = ? WHERE id = ?');
        return $stmt->execute([$bio, $id]);
    }
}

/**
 * Service Class - получение данных с Setter Injection
 */
class NotificationService
{
    private EmailSender $emailSender;
    private PDO $pdo;
    private SmsSender $smsSender;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Setter Injection - зависимость устанавливается через метод
     */
    public function setEmailSender(EmailSender $emailSender): void
    {
        $this->emailSender = $emailSender;
    }

    public function setSmsSender(SmsSender $smsSender): void
    {
        $this->smsSender = $smsSender;
    }

    public function sendWelcomeNotification(int $userId): void
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Отправляем email
        $this->emailSender->send(
            $user['email'],
            'Welcome!',
            "Hello {$user['username']},\n\nWelcome to our service!"
        );

        // Отправляем SMS (если настроено)
        if (isset($this->smsSender)) {
            $this->smsSender->send(
                $user['phone'],
                "Welcome to our service, {$user['username']}!"
            );
        }
    }

    private function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

/**
 * Concrete Implementation - Twilio SMS
 */
class TwilioSmsSender implements SmsSender
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct(string $accountSid, string $authToken, string $fromNumber)
    {
        $this->accountSid = $accountSid;
        $this->authToken = $authToken;
        $this->fromNumber = $fromNumber;
    }

    public function send(string $to, string $message): bool
    {
        // Имитация отправки через Twilio
        echo "Twilio: Sending SMS to $to with message '$message'\n";
        return true;
    }
}

/**
 * Concrete Implementation - Mock SMS для тестирования
 */
class MockSmsSender implements SmsSender
{
    private array $sentMessages = [];

    public function send(string $to, string $message): bool
    {
        $this->sentMessages[] = [
            'to' => $to,
            'message' => $message,
        ];
        return true;
    }

    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }
}

/**
 * Service Class - Property Injection
 */
class PaymentService
{
    /** @var PDO */
    private $pdo;

    /** @var EmailSender */
    private $emailSender;

    /** @var Logger */
    private $logger;

    /**
     * Property Injection - зависимости устанавливаются через свойства
     */
    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    public function setEmailSender(EmailSender $emailSender): void
    {
        $this->emailSender = $emailSender;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function processPayment(int $userId, float $amount): bool
    {
        // Логируем начало операции
        $this->logger?->log("Processing payment for user $userId, amount: $amount");

        // Получаем информацию о пользователе
        $user = $this->getUserById($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // Проверяем баланс
        if ($user['balance'] < $amount) {
            throw new RuntimeException('Insufficient funds');
        }

        // Обновляем баланс
        $newBalance = $user['balance'] - $amount;
        $stmt = $this->pdo->prepare('UPDATE users SET balance = ? WHERE id = ?');
        $stmt->execute([$newBalance, $userId]);

        // Отправляем подтверждение
        $this->emailSender?->send(
            $user['email'],
            'Payment Confirmed',
            "Payment of $$amount has been successfully processed."
        );

        $this->logger?->log("Payment processed for user $userId, new balance: $newBalance");

        return true;
    }

    private function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

/**
 * Concrete Implementation - File Logger
 */
class FileLogger implements Logger
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function log(string $message): void
    {
        file_put_contents($this->filename, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
    }
}

/**
 * Concrete Implementation - Database Logger
 */
class DatabaseLogger implements Logger
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(string $message): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO logs (message) VALUES (?)');
        $stmt->execute([$message]);
    }
}

/**
 * Dependency Injection Container - простой контейнер
 */
class Container
{
    private array $services = [];
    private array $factories = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            if (!isset($this->factories[$id])) {
                throw new RuntimeException("Service $id not found");
            }
            $this->services[$id] = $this->factories[$id]($this);
        }
        return $this->services[$id];
    }
}

/**
 * Пример использования Dependency Injection Container
 *
 * Основные типы DI
 *    Constructor Injection — внедрение через конструктор
 *    Setter Injection — внедрение через сеттеры
 *    Property Injection — внедрение через свойства
 *
 * Как это работает:
 *    Зависимости объявляются через интерфейсы — классы зависят от абстракций, а не конкретных реализаций
 *    Зависимости предоставляются извне — объекты получают зависимости через конструктор, сеттеры или свойства
 *    Контейнер управления зависимостями — централизованно управляет созданием и внедрением зависимостей
 * Преимущества:
 *    Снижение связанности — классы зависят от интерфейсов, а не конкретных реализаций
 *    Улучшение тестируемости — легко подменять зависимости на моки
 *    Гибкость — можно легко менять реализации без изменения клиентского кода
 *    Поддержка принципов SOLID — особенно Dependency Inversion Principle
 *    Централизованное управление — все зависимости создаются в одном месте
 *
 * Недостатки:
 *    Сложность — требует больше кода и настройки
 *    Производительность — создание объектов через рефлексию может быть медленнее
 *    Избыточность — для простых приложений может быть излишним
 *    Диагностика проблем — сложнее отследить цепочку зависимостей
 *
 * Где используется в фреймворках:
 * 1. Laravel's Service Container
 *    // Регистрация сервиса
 *    $app->singleton(Connection::class, function ($app) {
 *        return new Connection($app['config']['database']);
 *    });
 *    // Внедрение зависимости
 *    class UserController extends Controller {
 *        protected $connection;
 *        public function __construct(Connection $connection) {
 *            $this->connection = $connection;
 *        }
 *    }
 *    // Использование
 *    $user = App::make(UserController::class);
 * 2. Laravel's Facades (статический доступ)
 *    // Facade как "статический" доступ к сервисам
 *    Route::get('/', function () {
 *        return Cache::get('key');
 *    });
 *    // Или
 *    $value = Cache::remember('users', 60, function () {
 *        return DB::table('users')->get();
 *    });
 * 3. Laravel's Service Providers
 *    // Service Provider для регистрации сервисов
 *    class AppServiceProvider extends ServiceProvider {
 *        public function register() {
 *            $this->app->singleton(PaymentGateway::class, function ($app) {
 *                return new StripeGateway(config('services.stripe'));
 *            });
 *        }
 *    }
 * 4. Laravel's Automatic Dependency Resolution
 *    // Автоматическое внедрение зависимостей
 *    class OrderProcessor {
 *        protected $mailer;
 *        protected $repository;
 *        public function __construct(Mailer $mailer, OrderRepository $repository) {
 *            $this->mailer = $mailer;
 *            $this->repository = $repository;
 *        }
 *    }
 *    // Laravel автоматически внедрит зависимости
 *    $orderProcessor = app(OrderProcessor::class);
 * 5. Laravel's Contextual Binding
 *    // Разные реализации для разных контекстов
 *    $this->app->when(ShoppingCart::class)
 *        ->needs(CouponRepository::class)
 *        ->give(CouponRepository::class . '@mysql');
 *    $this->app->when(ShoppingCart::class)
 *        ->needs(CouponRepository::class)
 *        ->give(CouponRepository::class . '@redis');
 * 6. Symfony's DependencyInjection Component
 *    // Symfony DI Component
 *    use Symfony\Component\DependencyInjection\ContainerBuilder;
 *    use Symfony\Component\DependencyInjection\Definition;
 *    $container = new ContainerBuilder();
 *    $container->setDefinition('mail_sender', new Definition(SmtpEmailSender::class, [
 *        '%smtp.host%',
 *        '%smtp.port%',
 *        '%smtp.username%',
 *        '%smtp.password%',
 *    ]));
 *    // Внедрение
 *    $mailer = $container->get('mail_sender');
 * 7. Symfony's Service Tags
 *    // Теги для автоматической конфигурации
 *    $container->register('app.event_subscriber', EventSubscriber::class)
 *        ->addTag('kernel.event_subscriber');
 * 8. Zend Framework's Service Manager
 *    // Zend Service Manager
 *    $serviceManager = new ServiceManager();
 *    $serviceManager->setFactory('EmailSender', function ($sm) {
 *        return new SmtpEmailSender('host', 587, 'user', 'pass');
 *    });
 *    // Внедрение
 *    $emailSender = $serviceManager->get('EmailSender');
 * 9. Yii Framework's Dependency Injection
 *    // Yii DI Container
 *    $container = new yii\di\Container();
 *    $container->set('emailSender', ['class' => SmtpEmailSender::class, 'host' => 'smtp.example.com']);
 *    // Внедрение
 *    $emailSender = $container->get('emailSender');
 * 10. CakePHP's Dependency Injection
 *    // CakePHP DI через конструктор
 *    class UsersController extends AppController {
 *        public function __construct(EmailSender $emailSender) {
 *            $this->emailSender = $emailSender;
 *        }
 *    }
 * 11. Nette Framework's DI Container
 *    // Nette DI через аннотации
 *    / * *
 *    * @inject
 *    * @var EmailSender
 *    * /
 *    public $emailSender;
 *    // Или через конструктор
 *    class UserService {
 *        public function __construct(EmailSender $emailSender) {
 *           $this->emailSender = $emailSender;
 *        }
 *    }
 * 12. Doctrine ORM's Dependency Injection
 *    // Doctrine ORM
 *    $entityManager = EntityManager::create($dbParams, $config);
 *    // Репозитории автоматически получают EntityManager
 *    class UserRepository extends EntityRepository {
 *        public function findActiveUsers(): array {
 *            return $this->createQueryBuilder('u')
 *                ->where('u.isActive = :active')
 *                ->setParameter('active', true)
 *                ->getQuery()
 *                ->getResult();
 *        }
 *    }
 *
 * Когда полезен:
 *    Большие приложения — когда нужно управлять множеством зависимостей
 *    Тестирование — когда нужно легко подменять реальные зависимости на моки
 *    Гибкость — когда нужно менять реализации без изменения бизнес-логики
 *    Поддержка — когда код нужно легко поддерживать и расширять
 *    Принципы SOLID — а именно "D" = Dependency Inversion Principle
 *
 */


try {
    // Создаем контейнер
    $container = new Container();

    // Настраиваем зависимости
    $container->set('pdo', function (Container $c): PDO {
        return new PDO('sqlite::memory:');
    });

    $container->set('emailSender', function (Container $c): EmailSender {
        // Можно использовать разные реализации

        return new SmtpEmailSender('smtp.example.com', 587, 'user', 'pass');
        // Или: return new SendGridEmailSender('my-api-key');
    });

    $container->set('smsSender', function (Container $c): SmsSender {
        return new TwilioSmsSender('sid', 'token', '+1234567890');
    });

    $container->set('logger', function (Container $c): Logger {
        return new FileLogger('app.log');
    });

    $container->set('userService', function (Container $c): UserService {
        return new UserService(
            $c->get('emailSender'),
            $c->get('pdo')
        );
    });

    $container->set('profileService', function (Container $c): UserProfileService {
        return new UserProfileService($c->get('pdo'));
    });

    $container->set('notificationService', function (Container $c): NotificationService {
        $service = new NotificationService($c->get('pdo'));
        $service->setEmailSender($c->get('emailSender'));
        $service->setSmsSender($c->get('smsSender'));

        return $service;
    });

    $container->set('paymentService', function (Container $c): PaymentService {
        $service = new PaymentService();
        $service->setPdo($c->get('pdo'));
        $service->setEmailSender($c->get('emailSender'));
        $service->setLogger($c->get('logger'));

        return $service;
    });

    // Создаем базу данных
    $pdo = $container->get('pdo');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            balance DECIMAL(10,2) DEFAULT 0,
            bio TEXT,
            phone TEXT
        )'
    );
    $pdo->exec('CREATE TABLE logs (id INTEGER PRIMARY KEY, message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

    // Используем сервисы
    echo "=== User Registration ===\n";
    $userService = $container->get('userService');
    $userService->register('john_doe', 'john@example.com', 'password123');
    $userService->register('jane_smith', 'jane@example.com', 'securepass456');

    echo "\n=== User Profile Update ===\n";
    $profileService = $container->get('profileService');
    $profileService->updateProfile(1, 'Software developer and coffee lover');
    $user = $profileService->getUserById(1);
    echo 'User 1 bio: ' . ($user['bio'] ?? 'N/A') . "\n";

    echo "\n=== Welcome Notification ===\n";
    $notificationService = $container->get('notificationService');
    $notificationService->sendWelcomeNotification(2);

    echo "\n=== Payment Processing ===\n";
    $paymentService = $container->get('paymentService');
    $paymentService->processPayment(1, 50.00);

    echo "\n=== Logs ===\n";
    $logger = $container->get('logger');
    // В реальном приложении логи читаются из файла или базы данных

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/*  Для 8.4 *****************************************

// Использование атрибутов для автоматического внедрения
#[Inject]
private EmailSender $emailSender;

#[Inject(\'custom_logger\')]
private Logger $logger;

// Генерация контейнера через атрибуты
class ContainerBuilder
{
    public function build(object $target): void
    {
        $reflector = new ReflectionClass($target);

        foreach ($reflector->getProperties() as $property) {
            $attributes = $property->getAttributes(Inject::class);
            foreach ($attributes as $attribute) {
                // @var Inject $inject
                $inject = $attribute->newInstance();
                $serviceId = $inject->value ?? $property->getName();
                $property->setValue($target, $this->get($serviceId));
            }
        }
    }
}

// Pattern matching для автоматического разрешения зависимостей
public function resolve(string $class): object
{
    $reflector = new ReflectionClass($class);
    $constructor = $reflector->getConstructor();

    if ($constructor === null) {
        return $reflector->newInstance();
    }

    $parameters = $constructor->getParameters();
    $dependencies = [];

    foreach ($parameters as $parameter) {
        $dependencyClass = $parameter->getType();
        if ($dependencyClass instanceof ReflectionUnionType) {
            // Обработка Union Types

            foreach ($dependencyClass->getTypes() as $type) {
                if ($this->has($type->getName())) {
                    $dependencies[] = $this->get($type->getName());

                    continue 2;
                }
            }

            throw new \RuntimeException("Can\'t resolve dependency for {$parameter->getName()}");
        } elseif ($dependencyClass instanceof ReflectionNamedType && !$dependencyClass->isBuiltin()) {
            $dependencies[] = $this->get($dependencyClass->getName());
        } else {
            // Для примитивных типов можно использовать конфигурацию
            $defaultValue = $parameter->getDefaultValue();
            $dependencies[] = $defaultValue;
        }
    }

    return $reflector->newInstanceArgs($dependencies);
}

// Enum для типов внедрения
enum InjectionType: string
{
    case CONSTRUCTOR = \'constructor\';
    case SETTER = \'setter\';
    case PROPERTY = \'property\';
}

class Injector
{
    public function inject(object $target, InjectionType $type, array $dependencies): void
    {
        match ($type) {
            InjectionType::CONSTRUCTOR => $this->injectConstructor($target, $dependencies),
            InjectionType::SETTER => $this->injectSetter($target, $dependencies),
            InjectionType::PROPERTY => $this->injectProperty($target, $dependencies),
        };
    }

    private function injectConstructor(object $target, array $dependencies): void
    {
        $reflector = new ReflectionClass($target);
        $constructor = $reflector->getConstructor();

        if ($constructor !== null) {
            $constructor->invokeArgs($target, $dependencies);
        }
    }

    private function injectSetter(object $target, array $dependencies): void
    {
        $reflector = new ReflectionClass($target);

        foreach ($dependencies as $property => $value) {
            $method = \'set\' . ucfirst($property);

            if ($reflector->hasMethod($method)) {
                $reflector->getMethod($method)->invoke($target, $value);
            }
        }
    }

    private function injectProperty(object $target, array $dependencies): void
    {
        foreach ($dependencies as $property => $value) {
            if (property_exists($target, $property)) {
                $reflector = new ReflectionClass($target);

                $propertyRef = $reflector->getProperty($property);
                $propertyRef->setAccessible(true);
                $propertyRef->setValue($target, $value);
            }
        }
    }
}

******************************************************  */

<?php

declare(strict_types=1);

namespace Structural\Decorator;

/**
 * Component Interface - общий интерфейс для всех компонентов
 */
interface Coffee
{
    public function cost(): float;
    public function description(): string;
}

/**
 * Concrete Component - базовый кофе
 */
class SimpleCoffee implements Coffee
{
    public function cost(): float
    {
        return 2.50;
    }

    public function description(): string
    {
        return 'Simple coffee';
    }
}

/**
 * Concrete Component - эспрессо
 */
class Espresso implements Coffee
{
    public function cost(): float
    {
        return 3.00;
    }

    public function description(): string
    {
        return 'Espresso';
    }
}

/**
 * Concrete Component - капучино
 */
class Cappuccino implements Coffee
{
    public function cost(): float
    {
        return 4.50;
    }

    public function description(): string
    {
        return 'Cappuccino';
    }
}

/**
 * Decorator Base - базовый декоратор
 */
abstract class CoffeeDecorator implements Coffee
{
    protected Coffee $coffee;

    public function __construct(Coffee $coffee)
    {
        $this->coffee = $coffee;
    }

    public function cost(): float
    {
        return $this->coffee->cost();
    }

    public function description(): string
    {
        return $this->coffee->description();
    }
}

/**
 * Concrete Decorators - добавки
 */
class Milk extends CoffeeDecorator
{
    public function cost(): float
    {
        return $this->coffee->cost() + 0.50;
    }

    public function description(): string
    {
        return $this->coffee->description() . ', milk';
    }
}

class Sugar extends CoffeeDecorator
{
    public function cost(): float
    {
        return $this->coffee->cost() + 0.20;
    }

    public function description(): string
    {
        return $this->coffee->description() . ', sugar';
    }
}

class WhippedCream extends CoffeeDecorator
{
    public function cost(): float
    {
        return $this->coffee->cost() + 0.75;
    }

    public function description(): string
    {
        return $this->coffee->description() . ', whipped cream';
    }
}

class Chocolate extends CoffeeDecorator
{
    public function cost(): float
    {
        return $this->coffee->cost() + 0.60;
    }

    public function description(): string
    {
        return $this->coffee->description() . ', chocolate';
    }
}

class Caramel extends CoffeeDecorator
{
    public function cost(): float
    {
        return $this->coffee->cost() + 0.65;
    }

    public function description(): string
    {
        return $this->coffee->description() . ', caramel';
    }
}

/**
 * Пример 2: Декоратор для логирования
 */
interface Logger
{
    public function log(string $message): void;
}

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

abstract class LoggerDecorator implements Logger
{
    protected Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $message): void
    {
        $this->logger->log($message);
    }
}

class EmailLogger extends LoggerDecorator
{
    private string $email;

    public function __construct(Logger $logger, string $email)
    {
        parent::__construct($logger);
        $this->email = $email;
    }

    public function log(string $message): void
    {
        // Логируем в файл
        $this->logger->log($message);

        // Также отправляем email
        mail($this->email, 'Log message', $message);
    }
}

class DatabaseLogger extends LoggerDecorator
{
    private \PDO $pdo;

    public function __construct(Logger $logger, \PDO $pdo)
    {
        parent::__construct($logger);
        $this->pdo = $pdo;
    }

    public function log(string $message): void
    {
        $this->logger->log($message);

        // Сохраняем в базу данных
        $stmt = $this->pdo->prepare('INSERT INTO logs (message) VALUES (:message)');
        $stmt->execute(['message' => $message]);
    }
}

/**
 * Пример 3: Декоратор для кэширования
 */
interface Cache
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function has(string $key): bool;
}

class ArrayCache implements Cache
{
    private array $storage = [];

    public function get(string $key): mixed
    {
        return $this->storage[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->storage[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }
}

abstract class CacheDecorator implements Cache
{
    protected Cache $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
}

class RedisCacheDecorator extends CacheDecorator
{
    private \Redis $redis;

    public function __construct(Cache $cache, \Redis $redis)
    {
        parent::__construct($cache);
        $this->redis = $redis;
    }

    public function get(string $key): mixed
    {
        // Сначала проверяем в Redis
        $value = $this->redis->get($key);
        if ($value !== false) {
            return unserialize($value);
        }

        // Если нет в Redis, берем из внутреннего кэша
        $value = $this->cache->get($key);
        if ($value !== null) {
            // Сохраняем в Redis для будущего использования
            $this->redis->set($key, serialize($value), 'EX', 3600);
        }

        return $value;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        // Сохраняем в Redis
        $this->redis->set($key, serialize($value), 'EX', $ttl);

        // Также сохраняем в внутренний кэш
        $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): void
    {
        $this->redis->delete($key);
        $this->cache->delete($key);
    }

    public function has(string $key): bool
    {
        if ($this->redis->exists($key)) {
            return true;
        }

        return $this->cache->has($key);
    }
}

/**
 * Пример 4: Декоратор для валидации
 */
interface Validator
{
    public function validate(mixed $data): bool;
    public function getErrors(): array;
}

class UserValidator implements Validator
{
    private array $errors = [];

    public function validate(mixed $data): bool
    {
        $this->errors = [];

        if (!is_array($data)) {
            $this->errors[] = 'Data must be an array';
            return false;
        }

        if (empty($data['username'])) {
            $this->errors[] = 'Username is required';
        }

        if (empty($data['email'])) {
            $this->errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
        }

        if (isset($data['age']) && (!is_int($data['age']) || $data['age'] < 18)) {
            $this->errors[] = 'Age must be an integer and at least 18';
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

abstract class ValidatorDecorator implements Validator
{
    protected Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function validate(mixed $data): bool
    {
        return $this->validator->validate($data);
    }

    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }
}

class PasswordValidator extends ValidatorDecorator
{
    public function validate(mixed $data): bool
    {
        $isValid = $this->validator->validate($data);

        if (!$isValid) {
            return false;
        }

        if (empty($data['password'])) {
            $this->validator->getErrors()[] = 'Password is required';
            return false;
        }

        if (strlen($data['password']) < 8) {
            $this->validator->getErrors()[] = 'Password must be at least 8 characters';
            return false;
        }

        if (!preg_match('/[A-Z]/', $data['password'])) {
            $this->validator->getErrors()[] = 'Password must contain at least one uppercase letter';
            return false;
        }

        if (!preg_match('/[a-z]/', $data['password'])) {
            $this->validator->getErrors()[] = 'Password must contain at least one lowercase letter';
            return false;
        }

        if (!preg_match('/\d/', $data['password'])) {
            $this->validator->getErrors()[] = 'Password must contain at least one number';
            return false;
        }

        return true;
    }
}

class UniqueValidator extends ValidatorDecorator
{
    private \PDO $pdo;
    private string $table;

    public function __construct(Validator $validator, \PDO $pdo, string $table)
    {
        parent::__construct($validator);
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function validate(mixed $data): bool
    {
        $isValid = $this->validator->validate($data);

        if (!$isValid) {
            return false;
        }

        // Проверяем уникальность email
        if (!empty($data['email'])) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $this->validator->getErrors()[] = 'Email already exists';
                return false;
            }
        }

        return true;
    }
}


/**
 * Клиентский код
 *
 * Основная идея:
 *    Component — общий интерфейс для объектов, которые могут быть декорированы
 *    ConcreteComponent — конкретная реализация компонента
 *    Decorator — базовый декоратор, который содержит ссылку на компонент
 *    ConcreteDecorators — конкретные декораторы, добавляющие новые обязанности
 *
 * Как это работает:
 *    Component — общий интерфейс для всех компонентов
 *    ConcreteComponent — конкретная реализация компонента
 *    Decorator — базовый декоратор, который содержит ссылку на компонент
 *    ConcreteDecorators — конкретные декораторы, добавляющие новые обязанности
 *
 * Преимущества:
 *    Гибкость — можно добавлять обязанности динамически
 *    Альтернатива наследованию — избегает перегруженных иерархий
 *    Повторное использование кода — декораторы можно комбинировать
 *    Расширяемость — легко добавлять новые декораторы
 *    Прозрачность — клиентский код не знает о декораторах
 * Недостатки:
 *    Сложность — много маленьких объектов
 *    Производительность — дополнительные уровни косвенности
 *    Дизайн — может усложнить архитектуру
 *    Отладка — сложнее отследить цепочку декораторов
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Middleware
 *    // Middleware - классический декоратор для HTTP запросов
 *    Route::middleware(['auth', 'verified'])->group(function () {
 *       // Защищенные маршруты
 *    });
 *    // Пример middleware
 *    class EnsureEmailIsVerified {
 *       public function handle($request, Closure $next) {
 *          if (! $request->user()->hasVerifiedEmail()) {
 *             return redirect()->route('verification.notice');
 *          }
 *          return $next($request);
 *       }
 *    }
 * 2. Laravel's Pipeline
 *    // Pipeline - декоратор для обработки запросов
 *    $pipeline = app(Pipeline::class);
 *    $response = $pipeline
 *        ->send($request)
 *        ->through([
 *             EncryptCookies::class,
 *             AddQueuedCookiesToResponse::class,
 *             VerifyCsrfToken::class,
 *             // ... другие middleware
 *        ])
 *        ->then(function ($request) {
 *            // Конечный обработчик
 *    });
 * 3. Laravel's Eloquent Accessors & Mutators
 *    // Accessors и mutators как декораторы для атрибутов модели
 *    class User extends Model {
 *        protected $casts = [
 *            'email_verified_at' => 'datetime',
 *        ];
 *        public function getEmailAttribute($value) {
 *            return strtolower($value);
 *        }
 *        public function setEmailAttribute($value) {
 *            $this->attributes['email'] = strtolower($value);
 *        }
 *    }
 * 4. Laravel's Global Scopes
 *    // Global scopes - декораторы для всех запросов модели
 *    class UserScope implements Scope {
 *        public function apply(Builder $builder, Model $model) {
 *            $builder->where('active', 1);
 *        }
 *    }
 *    // Применение
 *    User::addGlobalScope(new UserScope);
 * 5. Laravel's Macroable Trait
 *    // Macroable - динамическое добавление методов
 *    class Str {
 *        use Macroable;
 *    }
 *    // Добавляем новый метод
 *    Str::macro('prefix', function ($string, $prefix) {
 *        return $prefix . $string;
 *    });
 *    // Использование
 *    $result = Str::prefix('world', 'hello ');
 * 6. Laravel's Event Broadcasting
 *    // Broadcasting - декоратор для событий
 *    class PodcastWasPurchased {
 *        use SerializesModels;
 *        public $podcast;
 *        public function __construct(Podcast $podcast) {
 *            $this->podcast = $podcast;
 *        }
 *        public function broadcastOn() {
 *            return new PrivateChannel('user.' . $this->user->id);
 *        }
 *    }
 * 7. Laravel's Notification Channels
 *    // Notification channels - декораторы для уведомлений
 *    class InvoicePaid extends Notification {
 *        public function via($notifiable) {
 *            return ['mail', 'database', 'nexmo'];
 *        }
 *        public function toMail($notifiable) {
 *            return (new MailMessage)
 *                ->line('The introduction to the invoice will be here')
 *                ->action('View Invoice', url('/invoice/$this->invoice->id'))
 *                ->line('Thank you for using our application!');
 *        }
 *    }
 * 8. Laravel's Service Container Decorators
 *    // Service container - декораторы для сервисов
 *    app()->singleton(PaymentGateway::class, function ($app) {
 *        return new StripeGateway(config('services.stripe'));
 *    });
 *    // Декоратор с логгированием
 *    app()->singleton(PaymentGateway::class, function ($app) {
 *        return new LoggingPaymentGateway(
 *            new StripeGateway(config('services.stripe')),
 *            new FileLogger(storage_path('logs/payment.log'))
 *        );
 *    });
 * 9. Laravel's HTTP Client Middleware
 *    // HTTP Client middleware - декораторы для HTTP запросов
 *    $response = Http::withHeaders(['X-Custom' => 'value'])
 *        ->timeout(5)
 *        ->withOptions([
 *            'verify' => false,
 *        ])
 *        ->get('https://api.example.com/data');
 * 10. Laravel's Query Builder Macros
 *    // Query builder macros - динамическое добавление методов
 *    Builder::macro('whereJsonContains', function ($column, $value) {
 *        return $this->whereRaw("JSON_CONTAINS(JSON_EXTRACT(`$column`, '$'), '\"$value\"') = 1");
 *    });
 *    // Использование
 *    $users = DB::table('users')
 *        ->whereJsonContains('tags', 'admin')
 *        ->get();
 * 11. Symfony's Event Subscribers
 *    // Event subscribers - декораторы для событий
 *    class EmailNotifier implements EventSubscriberInterface {
 *        public static function getSubscribedEvents() {
 *            return [
 *                OrderPlacedEvent::class => 'onOrderPlaced',
 *                UserRegisteredEvent::class => 'onUserRegistered',
 *            ];
 *        }
 *        public function onOrderPlaced(OrderPlacedEvent $event) {
 *            // Отправка email
 *        }
 *    }
 * 12. Symfony's HttpKernel Middleware
 *    // HttpKernel middleware - декораторы для обработки запросов
 *    class ProfilerMiddleware {
 *        public function handle(Request $request, callable $next) {
 *            $start = microtime(true);
 *            $response = $next($request);
 *            $duration = microtime(true) - $start;
 *            // Логирование времени обработки
 *            file_put_contents(
 *                'profiler.log',
 *                $request->getPathInfo() . " - " . $duration . "s\n",
 *                FILE_APPEND
 *            );
 *            return $response;
 *        }
 *    }
 *
 * Когда полезен:
 *    Динамическое добавление обязанностей — когда нужно добавлять функциональность во время выполнения
 *    Альтернатива наследованию — когда наследование создает слишком много классов
 *    Прозрачное добавление функциональности — клиентский код не должен знать о декораторах
 *    Повторное использование кода — декораторы можно комбинировать
 *    Фильтрация/трансформация — логирование, валидация, кэширование
 */

echo "=== Coffee Decorator Example ===\n";

// Создаем базовый кофе
$coffee = new SimpleCoffee();
echo $coffee->description() . ' - $' . number_format($coffee->cost(), 2) . "\n";

// Добавляем молоко
$coffee = new Milk($coffee);
echo $coffee->description() . ' - $' . number_format($coffee->cost(), 2) . "\n";

// Добавляем сахар
$coffee = new Sugar($coffee);
echo $coffee->description() . ' - $' . number_format($coffee->cost(), 2) . "\n";

// Добавляем взбитые сливки
$coffee = new WhippedCream($coffee);
echo $coffee->description() . ' - $' . number_format($coffee->cost(), 2) . "\n";

echo "\n=== Custom Coffee ===\n";
$specialCoffee = new Chocolate(new Caramel(new Espresso()));
echo $specialCoffee->description() . ' - $' . number_format($specialCoffee->cost(), 2) . "\n";


echo "\n=== Logger Decorator Example ===\n";

// Создаем файловый логгер
$fileLogger = new FileLogger('app.log');
$fileLogger->log('System started');

// Добавляем email уведомления
$emailLogger = new EmailLogger($fileLogger, 'admin@example.com');
$emailLogger->log('User logged in');

// Добавляем логирование в базу данных
try {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE logs (id INTEGER PRIMARY KEY, message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

    $dbLogger = new DatabaseLogger($emailLogger, $pdo);
    $dbLogger->log('Database connection established');

    // Проверяем логи в базе
    $logs = $pdo->query('SELECT * FROM logs')->fetchAll(\PDO::FETCH_ASSOC);
    echo "Logs in database:\n";
    foreach ($logs as $log) {
        echo ' - ' . $log['message'] . "\n";
    }

} catch (\Exception $e) {
    echo 'Database error: ' . $e->getMessage() . "\n";
}


echo "\n=== Cache Decorator Example ===\n";

// Создаем внутренний кэш
$cache = new ArrayCache();
$cache->set('user:1', ['name' => 'John', 'email' => 'john@example.com']);

// Добавляем Redis кэширование
try {
    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);

    $decoratedCache = new RedisCacheDecorator($cache, $redis);

    // Получаем данные
    $user = $decoratedCache->get('user:1');
    echo 'User from cache: ' . json_encode($user) . "\n";

    // Проверяем наличие ключа
    echo 'Has user:2? ' . ($decoratedCache->has('user:2') ? 'YES' : 'NO') . "\n";

    // Добавляем новый ключ
    $decoratedCache->set('user:2', ['name' => 'Jane', 'email' => 'jane@example.com']);
    echo "User:2 added\n";

} catch (\RedisException $e) {
    echo 'Redis error: ' . $e->getMessage() . "\n";
    // Fallback на внутренний кэш
    echo 'Using fallback cache: ' . json_encode($cache->get('user:1')) . "\n";
}


echo "\n=== Validator Decorator Example ===\n";

// Создаем базовый валидатор
$validator = new UserValidator();

// Валидируем данные
$data = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'age' => 25
];

if ($validator->validate($data)) {
    echo "Basic validation passed!\n";
} else {
    echo 'Basic validation failed: ' . implode(', ', $validator->getErrors()) . "\n";
}

// Добавляем валидацию пароля
$passwordValidator = new PasswordValidator($validator);
$dataWithPassword = $data + ['password' => 'Secret123'];

if ($passwordValidator->validate($dataWithPassword)) {
    echo "Password validation passed!\n";
} else {
    echo 'Password validation failed: ' . implode(', ', $passwordValidator->getErrors()) . "\n";
}

// Добавляем проверку уникальности email
try {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT UNIQUE)');

    $uniqueValidator = new UniqueValidator($passwordValidator, $pdo, 'users');

    // Данные с уникальным email
    $uniqueData = [
        'username' => 'new_user',
        'email' => 'new_user@example.com',
        'age' => 30,
        'password' => 'StrongPass123'
    ];

    if ($uniqueValidator->validate($uniqueData)) {
        echo "All validations passed! User can be registered.\n";
    }

} catch (\Exception $e) {
    echo 'Validation error: ' . $e->getMessage() . "\n";
}


/* Для 8.4 ******************************

// Использование атрибутов для декораторов
#[Decorator]
class Milk implements Coffee {}

// Генерация декораторов через атрибуты
class DecoratorBuilder
{
    public function build(string $componentClass, array $decorators = []): Coffee
    {
        $reflector = new ReflectionClass($componentClass);
        $instance = $reflector->newInstance();

        foreach (array_reverse($decorators) as $decorator) {
            $decoratorReflector = new ReflectionClass($decorator);
            $instance = $decoratorReflector->newInstance($instance);
        }

        return $instance;
    }
}

// Pattern matching для определения декораторов
public function getCoffee(string $type, array $additions = []): Coffee
{
    $coffee = match ($type) {
    'simple' => new SimpleCoffee(),
        'espresso' => new Espresso(),
        'cappuccino' => new Cappuccino(),
        default => throw new InvalidArgumentException("Unknown coffee type: $type"),
    };

    foreach ($additions as $addition) {
        $coffee = match ($addition) {
        'milk' => new Milk($coffee),
            'sugar' => new Sugar($coffee),
            'cream' => new WhippedCream($coffee),
            'chocolate' => new Chocolate($coffee),
            'caramel' => new Caramel($coffee),
            default => $coffee,
        };
    }

    return $coffee;
}

// Enum для типов кофе и добавок
enum CoffeeType: string
{
    case SIMPLE = 'simple';
    case ESPRESSO = 'espresso';
    case CAPPUCCINO = 'cappuccino';
}

enum CoffeeAddition: string
{
    case MILK = 'milk';
    case SUGAR = 'sugar';
    case CREAM = 'cream';
    case CHOCOLATE = 'chocolate';
    case CARAMEL = 'caramel';
}

class CoffeeFactory
{
    public function create(CoffeeType $type, array $additions = []): Coffee
    {
        $coffee = match ($type) {
        CoffeeType::SIMPLE => new SimpleCoffee(),
            CoffeeType::ESPRESSO => new Espresso(),
            CoffeeType::CAPPUCCINO => new Cappuccino(),
        };

        foreach ($additions as $addition) {
            $coffee = match ($addition) {
            CoffeeAddition::MILK => new Milk($coffee),
                CoffeeAddition::SUGAR => new Sugar($coffee),
                CoffeeAddition::CREAM => new WhippedCream($coffee),
                CoffeeAddition::CHOCOLATE => new Chocolate($coffee),
                CoffeeAddition::CARAMEL => new Caramel($coffee),
            };
        }

        return $coffee;
    }
}


*********************************** */

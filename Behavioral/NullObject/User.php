<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 16.06.2026 - 13:56
 */

namespace Behavioral\NullObject;

use Redis;
use RedisException;

/**
 * Interface - общий интерфейс
 */
interface UserInterface
{
    public function getName(): string;

    public function getEmail(): string;

    public function hasPermission(string $permission): bool;
}

/**
 * Пример 2: Логирование
 */
interface LoggerInterface
{
    public function log(string $message, string $level = 'info'): void;
}

/**
 * Пример 3: Кэширование
 */
interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 3600): void;

    public function has(string $key): bool;
}

/**
 * Class User
 * RealObject - реальный пользователь
 *
 * @package Behavioral\NullObject
 */
class User implements UserInterface
{
    public function __construct(
        private string $name,
        private string $email,
        private array  $permissions = []
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }
}

/**
 * Class NullUser
 * NullObject - "пустой" пользователь
 *
 * @package Behavioral\NullObject
 */
class NullUser implements UserInterface
{
    public function __construct(
        private readonly string $name = 'Guest',
        private readonly string $email = 'guest@example.com'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function hasPermission(string $permission): bool
    {
        return false;
    }
}

/**
 * Class UserRepository
 * Repository - репозиторий с поддержкой NullObject
 *
 * @package Behavioral\NullObject
 */
class UserRepository
{
    /** @var array<string, UserInterface> */
    private array $users = [];

    public function __construct()
    {
        // Предустановленные пользователи
        $this->users['admin'] = new User('Admin', 'admin@example.com', ['read', 'write', 'delete']);
        $this->users['user'] = new User('User', 'user@example.com', ['read']);
    }

    /**
     * @param string $username
     * @return UserInterface
     */
    public function find(string $username): UserInterface
    {
        return $this->users[$username] ?? new NullUser(); // Классика!
    }

    /**
     * @param UserInterface $user
     */
    public function add(UserInterface $user): void
    {
        if ($user instanceof User) {
            $this->users[$user->getName()] = $user;
        }
    }
}

/**
 * Class FileLogger
 *
 * @package Behavioral\NullObject
 */
class FileLogger implements LoggerInterface
{
    public function __construct(private string $filePath)
    {
    }

    public function log(string $message, string $level = 'info'): void
    {
        $entry = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
        file_put_contents($this->filePath, $entry, FILE_APPEND);
    }
}

/**
 * Class NullLogger
 *
 * @package Behavioral\NullObject
 */
class NullLogger implements LoggerInterface
{
    public function log(string $message, string $level = 'info'): void
    {
        // Ничего не делаем
    }
}

/**
 * Class RedisCache
 *
 * @package Behavioral\NullObject
 */
class RedisCache implements CacheInterface
{
    public function __construct(private Redis $redis)
    {
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @throws RedisException
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->redis->setex($key, $ttl, serialize($value));
    }

    /**
     * @param string $key
     * @return bool
     * @throws RedisException
     */
    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }
}

/**
 * Class NullCache
 *
 * @package Behavioral\NullObject
 */
class NullCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        // Ничего не делаем
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return false;
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Interface (UserInterface) - определяет контракт
 *    RealObject (User) - реальная реализация
 *    NullObject (NullUser) - "пустая" реализация, которая ничего не делает
 *    Клиентский код - работает с интерфейсом, не проверяя тип
 *
 * Преимущества:
 *    Нет проверок на null - клиентский код не требует if ($user !== null)
 *    Полиморфизм - NullObject можно использовать как любой другой объект
 *    Чистота кода - устраняет условные операторы
 *    Безопасность - предсказуемое поведение по умолчанию
 *
 * Недостатки:
 *    Скрытое поведение - может маскировать отсутствие реального объекта
 *    Лишние объекты - создание объектов, которые ничего не делают
 *    Сложность отладки - труднее отследить, почему ничего не происходит
 *
 * Где используется в фреймворках:
 * 1. Laravel's optional() Helper
 *    // Вместо проверки на null
 *    optional($user)->name; // Вернет null или имя
 *    // Аналогично моему NullUser
 *    $user = $user ?? new NullUser();
 *    echo $user->getName(); // Всегда работает
 * 2. Symfony's NullLogger
 *    use Psr\Log\NullLogger;
 *    use Psr\Log\LoggerInterface;
 *    class MyService
 *    {
 *        public function __construct(
 *            private LoggerInterface $logger = new NullLogger()
 *        ) {}
 *        public function doSomething(): void
 *        {
 *            $this->logger->info('Action performed'); // Никогда не упадет
 *        }
 *    }
 * 3. WordPress's WP_Error
 *    // Вместо null возвращается объект ошибки
 *    $result = some_function();
 *    if ($result instanceof WP_Error) {
 *        // Обработка ошибки
 *    } else {
 *        // Работа с результатом
 *    }
 *    // Аналогично - всегда объект, нет проверки на null
 * 4. Doctrine's NullOutput
 *    use Doctrine\Common\Proxy\NullOutput;
 *    $output = $configuration->getSQLLogger() ?? new NullOutput();
 *    $output->startQuery($sql); // Никогда не упадет
 * 5. Laravel's NullCache
 *    use Illuminate\Cache\NullStore;
 *    $cache = $cache ?? new NullStore();
 *    $cache->get('key'); // Всегда возвращает null
 * 6. Symfony's OptionsResolver
 *    $options = $resolver->resolve($options ?? []);
 *    // Вместо проверки на null, используются дефолтные значения
 *
 * Когда полезен:
 *    Логирование - когда логгер не сконфигурирован(настроен)
 *    Кэширование - когда кэш недоступен(отвалился)
 *    Авторизация - для гостевых пользователей(только просмотр)
 *    Валидация - для пустых значений
 *    Фабрики - когда невозможно создать реальный объект
 *    Тестирование - заглушки(dummy) вместо реальных зависимостей
 */

echo "=== User Repository Example ===\n";
$repo = new UserRepository();

// Найти существующего пользователя
$user = $repo->find('admin');
echo "User: {$user->getName()}, Email: {$user->getEmail()}, Can write? "
    . ($user->hasPermission('write') ? 'Yes' : 'No') . "\n";

// Найти несуществующего пользователя (NullObject)
$missing = $repo->find('unknown');
echo "Missing User: {$missing->getName()}, Can delete? "
    . ($missing->hasPermission('delete') ? 'Yes' : 'No') . "\n";

// Нет необходимости проверять на null!
if ($missing->hasPermission('anything')) {
    // Этот код никогда не выполнится
}


echo "\n=== Logger Example ===\n";
$logger = $repo->find('admin') instanceof User
    ? new FileLogger(__DIR__ . '/app.log')
    : new NullLogger();

$logger->log('User logged in');


echo "\n=== Cache Example ===\n";
$cache = new RedisCache(new Redis()); // Или NullCache в тестах

$cache->set('test_key', 'test_value');
echo 'Cache has test_key? ' . ($cache->has('test_key') ? 'Yes' : 'No') . "\n";

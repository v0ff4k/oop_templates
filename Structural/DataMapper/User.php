<?php

declare(strict_types=1);

namespace Structural\DataMapper;

use Exception;
use PDO;

/**
 * Data Mapper Interface
 */
interface UserMapperInterface
{
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findAll(): array;

    public function insert(User $user): void;

    public function update(User $user): void;

    public function delete(User $user): void;
}

/**
 * Entity - пользователь
 */
class User
{
    private ?int $id = null;
    private string $username;
    private string $email;
    private bool $isActive;
    private ?string $bio = null;

    public function __construct(string $username, string $email, bool $isActive = true)
    {
        $this->username = $username;
        $this->email = $email;
        $this->isActive = $isActive;
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): void
    {
        $this->bio = $bio;
    }
}

/**
 * Data Mapper Implementation
 */
class UserMapper implements UserMapperInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    private function mapRowToUser(array $row): User
    {
        $user = new User($row['username'], $row['email'], (bool)$row['is_active']);
        $user->setId((int)$row['id']);
        $user->setBio($row['bio'] ?? null);

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    public function insert(User $user): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, is_active, bio) VALUES (:username, :email, :isActive, :bio)'
        );
        $stmt->execute([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'isActive' => $user->isActive(),
            'bio' => $user->getBio(),
        ]);

        $user->setId((int)$this->pdo->lastInsertId());
    }

    public function update(User $user): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET username = :username, email = :email, is_active = :isActive, bio = :bio WHERE id = :id'
        );
        $stmt->execute([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'isActive' => $user->isActive(),
            'bio' => $user->getBio(),
        ]);
    }

    public function delete(User $user): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $user->getId()]);
    }
}

/**
 * Unit of Work Pattern (часто используется с DataMapper)
 */
class UnitOfWork
{
    private PDO $pdo;
    /** @var User[] */
    private array $newObjects = [];
    /** @var User[] */
    private array $dirtyObjects = [];
    /** @var User[] */
    private array $removedObjects = [];
    private UserMapper $mapper;

    public function __construct(PDO $pdo, UserMapper $mapper)
    {
        $this->pdo = $pdo;
        $this->mapper = $mapper;
    }

    public function registerNew(User $user): void
    {
        $this->newObjects[] = $user;
    }

    public function registerDirty(User $user): void
    {
        if (!$this->isInIdentityMap($user) && !in_array($user, $this->newObjects, true)) {
            $this->dirtyObjects[] = $user;
        }
    }

    private function isInIdentityMap(User $user): bool
    {
        foreach ($this->newObjects as $u) {
            if ($u === $user) {
                return true;
            }
        }
        foreach ($this->dirtyObjects as $u) {
            if ($u === $user) {
                return true;
            }
        }
        return false;
    }

    public function registerRemoved(User $user): void
    {
        // Удаляем из newObjects и dirtyObjects
        $this->newObjects = array_filter($this->newObjects, function (User $u) use ($user) {
            return $u !== $user;
        });
        $this->dirtyObjects = array_filter($this->dirtyObjects, function (User $u) use ($user) {
            return $u !== $user;
        });

        if (!in_array($user, $this->removedObjects, true)) {
            $this->removedObjects[] = $user;
        }
    }

    public function commit(): void
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($this->newObjects as $user) {
                $this->mapper->insert($user);
            }

            foreach ($this->dirtyObjects as $user) {
                $this->mapper->update($user);
            }

            foreach ($this->removedObjects as $user) {
                $this->mapper->delete($user);
            }

            $this->pdo->commit();

            // Очищаем
            $this->newObjects = [];
            $this->dirtyObjects = [];
            $this->removedObjects = [];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Entity — объект бизнес-логики (User)
 *    Data Mapper — отвечает за преобразование между объектом и БД
 *    Unit of Work — управляет транзакциями и отслеживает изменения
 *
 * Преимущества:
 *    Разделение ответственности — логика предметной области отделена от логики хранения
 *    Тестируемость — легко мокировать маппер для тестов
 *    Гибкость — можно менять хранилище без изменения сущностей
 *    Чистота объектов — сущности не знают о базе данных
 *    Кэширование — можно кэшировать объекты через Identity Map
 * Недостатки:
 *    Сложность — больше кода, чем в Active Record
 *    Производительность — дополнительный уровень косвенности
 *    Избыточность — для простых случаев может быть излишним
 *    Дизайн — требует предварительного планирования
 *
 * Где используется в фреймворках:
 * 1. Laravel's Eloquent (Active Record, но похоже на DataMapper)
 *    // Eloquent - это Active Record, но концептуально похож
 *    $user = User::find(1);
 *    $user->name = 'John';
 *    $user->save();
 * 2. Doctrine ORM (чистый DataMapper)
 *    // Doctrine ORM - классический DataMapper
 *    $user = $entityManager->find('App\User', 1);
 *    $user->setName('John');
 *    $entityManager->persist($user);
 *    $entityManager->flush();
 * 3. Laravel's Query Builder
 *    // Query Builder - низкоуровневый доступ
 *    $user = DB::table('users')->find(1);
 *    DB::table('users')->where('id', 1)->update(['name' => 'John']);
 * 4. Symfony's Doctrine Integration
 *    // Symfony + Doctrine
 *    $entityManager = $this->getDoctrine()->getManager();
 *    $user = $entityManager->find(User::class, $id);
 *    $entityManager->flush();
 * 5. Laravel's Model Events
 *    // Хуки для DataMapper
 *    User::creating(function ($user) {
 *        $user->created_at = now();
 *    });
 *    User::updating(function ($user) {
 *        $user->updated_at = now();
 *    });
 * 6. Laravel's Observers
 *    // Наблюдатели за моделями
 *    class UserObserver {
 *        public function creating(User $user) {
 *            // Действия перед созданием
 *        }
 *        public function updating(User $user) {
 *            // Действия перед обновлением
 *        }
 *    }
 * 7. Laravel's Model Relationships
 *    // Отношения между моделями
 *    class User extends Model {
 *        public function posts() {
 *            return $this->hasMany(Post::class);
 *        }
 *        public function comments() {
 *            return $this->hasMany(Comment::class);
 *        }
 *    }
 * 8. Laravel's Model Accessors & Mutators
 *    // Кастомные преобразования
 *    class User extends Model {
 *        public function getFullNameAttribute() {
 *            return $this->first_name . ' ' . $this->last_name;
 *        }
 *        public function setEmailAttribute($value) {
 *            $this->attributes['email'] = strtolower($value);
 *        }
 *    }
 * 9. Laravel's Model Casting
 *    // Автоматическое преобразование типов
 *    class User extends Model {
 *        protected $casts = [
 *            'is_active' => 'boolean',
 *            'created_at' => 'datetime',
 *            'settings' => 'array',
 *        ];
 *    }
 * 10. Laravel's Model Scopes
 *    // Переиспользуемые запросы
 *    class User extends Model {
 *        public function scopeActive($query) {
 *            return $query->where('is_active', true);
 *        }
 *        public function scopeAdmins($query) {
 *            return $query->where('role', 'admin');
 *        }
 *    }
 *    $activeAdmins = User::active()->admins()->get();
 *
 * Когда полезен:
 *    Сложные предметные области — много сущностей и связей
 *    Бизнес-логика — сложная логика, не связанная с хранением
 *    Тестирование — нужно легко мокировать хранилище
 *    Миграция — смена хранилища (SQL -> NoSQL)
 *    Производительность — оптимизация запросов и кэширование
 *
 */

// 1 Единственный маппер, по результатам БД
try {
    // Создаем PDO соединение
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создаем таблицу
    $pdo->exec(
        'CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT 1,
            bio TEXT
        )'
    );

    // Создаем маппер
    $mapper = new UserMapper($pdo);

    // Создаем Unit of Work
    $uow = new UnitOfWork($pdo, $mapper);

    // Создаем пользователей
    $user1 = new User('john_doe', 'john@example.com');
    $user2 = new User('jane_smith', 'jane@example.com', false);
    $user2->setBio('Software developer and tech enthusiast');

    // Регистрируем новые объекты
    $uow->registerNew($user1);
    $uow->registerNew($user2);

    // Сохраняем изменения
    $uow->commit();

    echo "Users created successfully!\n";

    // Загружаем пользователей
    $loadedUser = $mapper->findById(1);
    echo 'Loaded user: ' . $loadedUser->getUsername() . ' (ID: ' . $loadedUser->getId() . ")\n";

    // Обновляем пользователя
    $loadedUser->setBio('Updated bio: Loves PHP and design patterns');
    $uow->registerDirty($loadedUser);
    $uow->commit();

    echo "User updated successfully!\n";

    // Удаляем пользователя
    $userToDelete = $mapper->findById(2);
    if ($userToDelete) {
        $uow->registerRemoved($userToDelete);
        $uow->commit();
        echo "User deleted successfully!\n";
    }

    // Получаем всех пользователей
    $allUsers = $mapper->findAll();
    echo 'All users (' . count($allUsers) . "):\n";
    foreach ($allUsers as $user) {
        echo ' - ' . $user->getUsername() . ' (' . ($user->isActive() ? 'active' : 'inactive') . ")\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/* **************************************
* Расширение для  php 8.4

// Использование атрибутов для маппинга
#[Entity]
class User
{
    #[Id]
    #[Column(type: 'integer')]
    private ?int $id = null;

    #[Column(type: 'string')]
    private string $username;

    #[Column(type: 'string')]
    private string $email;

    #[Column(type: 'boolean')]
    private bool $isActive = true;

    #[Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    // Геттеры и сеттеры
}

// Генерация мапперов через атрибуты
class MapperBuilder
{
    public function build(string $entityClass): object
    {
        $reflector = new ReflectionClass($entityClass);
        $properties = $reflector->getProperties();

        $mapperCode = "class GeneratedMapper implements UserMapperInterface {\n";
        $mapperCode .= "    private \PDO \$pdo;\n\n";
        $mapperCode .= "    public function __construct(\PDO \$pdo) {\n";
        $mapperCode .= "        \$this->pdo = \$pdo;\n";
        $mapperCode .= "    }\n\n";

        // Метод findById
        $mapperCode .= "    public function findById(int \$id): ?User {\n";
        $mapperCode .= "        \$stmt = \$this->pdo->prepare('SELECT * FROM ' . \$this->getTableName() . ' WHERE id = :id');\n";
        $mapperCode .= "        \$stmt->execute(['id' => \$id]);\n";
        $mapperCode .= "        \$row = \$stmt->fetch(\PDO::FETCH_ASSOC);\n\n";
        $mapperCode .= "        if (!$row) {\n";
        $mapperCode .= "            return null;\n";
        $mapperCode .= "        }\n\n";
        $mapperCode .= "        return \$this->mapRowToUser(\$row);\n";
        $mapperCode .= "    }\n\n";

        // Метод insert
        $mapperCode .= "    public function insert(User \$user): void {\n";
        $mapperCode .= "        \$stmt = \$this->pdo->prepare('INSERT INTO ' . \$this->getTableName() . ' (username, email, is_active, bio) VALUES (:username, :email, :isActive, :bio)');\n";
        $mapperCode .= "        \$stmt->execute([\n";
        $mapperCode .= "            'username' => \$user->getUsername(),\n";
        $mapperCode .= "            'email' => \$user->getEmail(),\n";
        $mapperCode .= "            'isActive' => \$user->isActive(),\n";
        $mapperCode .= "            'bio' => \$user->getBio(),\n";
        $mapperCode .= "        ]);\n\n";
        $mapperCode .= "        \$user->setId((int)\$this->pdo->lastInsertId());\n";
        $mapperCode .= "    }\n\n";

        // ... остальные методы

        $mapperCode .= "    private function mapRowToUser(array \$row): User {\n";
        $mapperCode .= "        // Автоматическое создание объекта на основе атрибутов\n";
        $mapperCode .= "    }\n";

        $mapperCode .= "}\n";

        // эмулируем новый класс в памяти.
        eval($mapperCode);

        return new GeneratedMapper();
    }
}

// Шаблон соответствия(matching) для определения типа маппера
public function getMapper(string $type): UserMapperInterface
{
    return match ($type) {
        'user' => new UserMapper($pdo),
        'product' => new ProductMapper($pdo),
        'order' => new OrderMapper($pdo),
        default => throw new InvalidArgumentException("Unknown mapper type: $type"),
    };
}

// Enum для типов мапперов
enum MapperType: string
{
    case USER = 'user';
    case PRODUCT = 'product';
    case ORDER = 'order';
    case CATEGORY = 'category';
}

class MapperFactory
{
    public function create(MapperType $type, PDO $pdo): UserMapperInterface
    {
        return match ($type) {
            MapperType::USER => new UserMapper($pdo),
            MapperType::PRODUCT => new ProductMapper($pdo),
            MapperType::ORDER => new OrderMapper($pdo),
            MapperType::CATEGORY => new CategoryMapper($pdo),
        };
    }
}

*************************** */

<?php

declare(strict_types=1);

namespace Structural\Composite;

/**
 * Component Interface - общий интерфейс для всех компонентов
 */
interface Component
{
    public function add(Component $component): void;
    public function remove(Component $component): void;
    public function getChildren(): array;
    public function getName(): string;
    public function attackPower(): int;
    public function __toString(): string;
}

/**
 * Leaf - конечный объект (лист)
 */
abstract class Leaf implements Component
{
    protected string $name;
    protected int $power;

    public function __construct(string $name, int $power)
    {
        $this->name = $name;
        $this->power = $power;
    }

    public function add(Component $component): void
    {
        throw new UnitException(get_class($this) . " cannot add components");
    }

    public function remove(Component $component): void
    {
        throw new UnitException(get_class($this) . " cannot remove components");
    }

    public function getChildren(): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function attackPower(): int
    {
        return $this->power;
    }

    public function __toString(): string
    {
        return $this->name . " (Power: " . $this->power . ")";
    }
}

class UnitException extends  \Exception
{
    public function __construct($message = "", $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Composite - составной объект
 */
abstract class Composite implements Component
{
    protected string $name;
    protected array $children = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function add(Component $component): void
    {
        $this->children[] = $component;
    }

    public function remove(Component $component): void
    {
        $this->children = array_filter(
            $this->children,
            function (Component $child) use ($component) {
                return $child !== $component;
            }
        );
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        $childrenStr = implode(', ', array_map(fn($child) => (string)$child, $this->children));

        return $this->name . " (Children: " . ($childrenStr ?: 'none') . ")";
    }
}

/**
 * Concrete Leaf Classes
 */
class Archer extends Leaf
{
    public function __construct()
    {
        parent::__construct('Archer', 3);
    }
}

class Infantryman extends Leaf
{
    public function __construct()
    {
        parent::__construct('Infantryman', 5);
    }
}

class Cavalryman extends Leaf
{
    public function __construct()
    {
        parent::__construct('Cavalryman', 8);
    }
}

class Tank extends Leaf
{
    public function __construct()
    {
        parent::__construct('Tank', 20);
    }
}

class Helicopter extends Leaf
{
    public function __construct()
    {
        parent::__construct('Helicopter', 25);
    }
}

/**
 * Concrete Composite Classes
 */
class Army extends Composite
{
    public function attackPower(): int
    {
        $power = 0;

        foreach ($this->children as $child) {
            $power += $child->attackPower();
        }

        return $power;
    }
}

class Corps extends Composite
{
    public function attackPower(): int
    {
        $power = 0;

        foreach ($this->children as $child) {
            $power += $child->attackPower();
        }

        return $power;
    }
}

class Division extends Composite
{
    public function attackPower(): int
    {
        $power = 0;

        foreach ($this->children as $child) {
            $power += $child->attackPower();
        }

        return $power;
    }
}

class Squad extends Composite
{
    public function attackPower(): int
    {
        $power = 0;

        foreach ($this->children as $child) {
            $power += $child->attackPower();
        }

        return $power;
    }
}

class Transport extends Composite
{
    private int $capacity;
    private int $currentLoad = 0;

    public function __construct(string $name, int $capacity)
    {
        parent::__construct($name);
        $this->capacity = $capacity;
    }

    public function add(Component $component): void
    {
        $power = $component->attackPower();

        if ($this->currentLoad + $power > $this->capacity) {
            throw new UnitException("Transport overloaded! Cannot add: ".$component->getName());
        }

        $this->children[] = $component;
        $this->currentLoad += $power;
    }

    public function remove(Component $component): void
    {
        $this->children = array_filter(
            $this->children,
            function (Component $child) use ($component) {
                return $child !== $component;
            }
        );
        // Обновляем текущую загрузку
        $this->currentLoad = array_sum(
            array_map(
                fn($child) => $child->attackPower(),
                $this->children
            )
        );
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getCurrentLoad(): int
    {
        return $this->currentLoad;
    }

    public function attackPower(): int
    {
        $power = 0;
        foreach ($this->children as $child) {
            $power += $child->attackPower();
        }
        return $power;
    }

    public function __toString(): string
    {
        return parent::__toString() .
            " (Load: {$this->currentLoad}/{$this->capacity})";
    }
}

/**
 * Пример 2: FileSystem - Дерево файловой системы
 */
interface FileSystemElement
{
    public function name(): string;
    public function size(): int;
    public function print(string $indent = ""): string;
}

class File implements FileSystemElement
{
    private string $name;
    private int $size;

    public function __construct(string $name, int $size)
    {
        $this->name = $name;
        $this->size = $size;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function print(string $indent = ""): string
    {
        return $indent . $this->name . " (" . $this->size . " bytes)\n";
    }
}

class Directory implements FileSystemElement
{
    private string $name;
    /** @var FileSystemElement[] */
    private array $elements = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function add(FileSystemElement $element): void
    {
        $this->elements[] = $element;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function size(): int
    {
        $size = 0;

        foreach ($this->elements as $element) {
            $size += $element->size();
        }

        return $size;
    }

    public function print(string $indent = ""): string
    {
        $output = $indent . "D " . $this->name . " (" . $this->size() . " bytes)\n";

        foreach ($this->elements as $element) {
            $output .= $element->print($indent . "  ");
        }

        return $output;
    }
}

/**
 * Пример 3: Menu System - Иерархическое меню
 */
interface MenuItem
{
    public function getName(): string;
    public function getPrice(): float;
    public function isVegetarian(): bool;
    public function print(): string;
}

class SimpleMenuItem implements MenuItem
{
    private string $name;
    private float $price;
    private bool $vegetarian;

    public function __construct(string $name, float $price, bool $vegetarian)
    {
        $this->name = $name;
        $this->price = $price;
        $this->vegetarian = $vegetarian;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isVegetarian(): bool
    {
        return $this->vegetarian;
    }

    public function print(): string
    {
        $veg = $this->vegetarian ? "(v)" : "";
        return "  • " . $this->name . " " . $veg . " - $" . number_format($this->price, 2) . "\n";
    }
}

class MenuCategory implements MenuItem
{
    private string $name;
    /** @var MenuItem[] */
    private array $items = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function add(MenuItem $item): void
    {
        $this->items[] = $item;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        // Для категории цена не определена
        return 0;
    }

    public function isVegetarian(): bool
    {
        return false;
    }

    public function print(): string
    {
        $output = $this->name . ":\n";
        foreach ($this->items as $item) {
            $output .= $item->print();
        }
        return $output;
    }
}

/**
 * Пример 4: Company Organization Chart
 */
interface Employee
{
    public function getName(): string;
    public function getPosition(): string;
    public function getSalary(): float;
    public function add(Employee $employee): void;
    public function remove(Employee $employee): void;
    public function getSubordinates(): array;
    public function print(string $indent = ""): string;
}

class Manager implements Employee
{
    private string $name;
    private string $position;
    private float $salary;
    /** @var Employee[] */
    private array $subordinates = [];

    public function __construct(string $name, string $position, float $salary)
    {
        $this->name = $name;
        $this->position = $position;
        $this->salary = $salary;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getSalary(): float
    {
        return $this->salary;
    }

    public function add(Employee $employee): void
    {
        $this->subordinates[] = $employee;
    }

    public function remove(Employee $employee): void
    {
        $this->subordinates = array_filter(
            $this->subordinates,
            function (Employee $sub) use ($employee) {
                return $sub !== $employee;
        });
    }

    public function getSubordinates(): array
    {
        return $this->subordinates;
    }

    public function print(string $indent = ""): string
    {
        $output = $indent . "Y " . $this->name . " (" . $this->position . ", $" . number_format($this->salary) . ")\n";

        foreach ($this->subordinates as $subordinate) {
            $output .= $subordinate->print($indent . "  ");
        }

        return $output;
    }
}

class Developer implements Employee
{
    private string $name;
    private string $position;
    private float $salary;

    public function __construct(string $name, string $position, float $salary)
    {
        $this->name = $name;
        $this->position = $position;
        $this->salary = $salary;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getSalary(): float
    {
        return $this->salary;
    }

    public function add(Employee $employee): void
    {
        throw new UnitException(get_class($this) . " cannot have subordinates");
    }

    public function remove(Employee $employee): void
    {
        throw new UnitException(get_class($this) . " cannot have subordinates");
    }

    public function getSubordinates(): array
    {
        return [];
    }

    public function print(string $indent = ""): string
    {
        $output = $indent . "PC " . $this->name . " (" . $this->position . ", $" . number_format($this->salary) . ")\n";
        return $output;
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Component Interface - общий интерфейс для всех компонентов
 *    Leaf - конечные объекты, которые не могут содержать другие компоненты
 *    Composite - составные объекты, которые могут содержать другие компоненты
 *    Client - работает с Component интерфейсом, не зная тип объекта
 *
 * Преимущества:
 *    Простота - клиентский код работает с единым интерфейсом
 *    Гибкость - легко добавлять новые типы компонентов
 *    Иерархия - естественное представление древовидных структур
 *    Переиспользование - одинаковый код для одиночных и составных объектов
 *    Расширяемость - можно легко добавлять новые операции
 * Недостатки:
 *    Универсальность - некоторые операции не подходят для листьев
 *    Производительность - рекурсивные операции могут быть медленными
 *    Дизайн - требует предварительного планирования иерархии
 *    Сложность - может быть излишним для простых случаев
 *
 * Где используется в фреймворках:
 * 1. Laravel's Eloquent Relationships
 *    // Древовидные отношения
 *    $user->posts; // Один-ко-многим
 *    $user->roles; // Многие-ко-многим
 *    $post->comments; // Вложенные комментарии
 *    // Пример иерархии
 *    $category = Category::find(1);
 *    foreach ($category->children as $child) {
 *       // Рекурсивный обход
 *    }
 * 2. Laravel's Notification Hierarchy
 *    // Уведомления могут содержать другие уведомления
 *    $notification = new OrderShipped($order);
 *    $notification->onQueue('processing');
 *    $notification->delay(60);
 *    // Иерархия каналов
 *    $channels = $notification->via($notifiable);
 *    foreach ($channels as $channel) {
 *       // Обработка каждого канала
 *    }
 * 3. Laravel's Menu Builder
 *    // Древовидное меню
 *    Menu::create('main', function ($menu) {
 *       $menu->add('Home', ['url' => '/']);
 *       $menu->add('About', ['url' => '/about']);
 *       $blog = $menu->add('Blog', ['url' => '/blog']);
 *       $blog->add('Latest', ['url' => '/blog/latest']);
 *       $blog->add('Categories', ['url' => '/blog/categories']);
 * });
 * 4. Laravel's Form Requests
 *    // Иерархия валидации
 *    $request->validate([
 *       'name' => 'required|string|max:255',
 *       'email' => 'required|email|unique:users',
 *       'posts' => 'array',
 *       'posts.*.title' => 'required|string',
 *    ]);
 *    // Древовидная валидация
 *    Validator::make($data, [
 *       'user.name' => 'required',
 *       'user.email' => 'required|email',
 *       'user.posts' => 'array',
 *       'user.posts.*.title' => 'required',
 *    ]);
 * 5. Laravel's Filesystem Tree
 *    // Дерево файловой системы
 *    Storage::disk('s3')->allFiles('/');
 *    Storage::disk('local')->directories('/');
 *    // Рекурсивный обход
 *    $files = Storage::allFiles('public/uploads');
 *    foreach ($files as $file) {
 *       // Обработка файлов
 *    }
 * 6. Laravel's Route Collections
 *    // Иерархия роутов
 *    Route::group(['middleware' => 'auth'], function () {
 *       Route::get('/dashboard', 'DashboardController@index');
 *       Route::group(['prefix' => 'admin'], function () {
 *          Route::get('/users', 'AdminUsersController@index');
 *          Route::get('/posts', 'AdminPostsController@index');
 *       });
 *    });
 * 7. Laravel's Collection Methods
 *    // Древовидные коллекции
 *    $collection = collect([
 *       ['name' => 'John', 'posts' => [...]],
 *       ['name' => 'Jane', 'posts' => [...]],
 *    ]);
 *    // Рекурсивные операции
 *    $collection->map(function ($user) {
 *       $user['posts_count'] = count($user['posts']);
 *       return $user;
 *    });
 * 8. Laravel's Config Hierarchy
 *    // Иерархия конфигураций
 *    config(['app.name' => 'MyApp']);
 *    config(['database.connections.mysql.host' => 'localhost']);
 *    // Рекурсивное получение
 *    $value = config('database.connections.mysql');
 * 9. Laravel's View Composers
 *    // Иерархия представлений
 *    View::composer('*', function ($view) {
 *       $view->with('global', 'Global data');
 *    });
 *    View::composer('admin.*', function ($view) {
 *       $view->with('admin', 'Admin data');
 *    });
 * 10. Laravel's Service Providers
 *    // Иерархия сервис-провайдеров
 *    class AppServiceProvider extends ServiceProvider {
 *       public function register() {
 *          $this->app->singleton(PaymentGateway::class, function ($app) {
 *             return new StripeGateway(config('services.stripe'));
 *          });
 *       }
 *    }
 *    class AuthServiceProvider extends ServiceProvider {
 *       protected $policies = [
 *          Model::class => Policy::class,
 *       ];
 *    }
 *
 * Когда полезен:
 *    Деревья - файловые системы, организационные структуры
 *    Иерархии - меню, категории, комментарии
 *    Группировка - армии, команды, коллекции
 *    Рекурсивные операции - обход, суммирование, фильтрация
 *    Единый интерфейс - клиентский код не должен знать о типе объекта
 */



echo "=== Army Composite Example v1 ===\n";

// Создаем армию
$army = new Army("Northern Army");

// Создаем корпус
$corps1 = new Corps("Shock Corps");
$corps2 = new Corps("Infantry Corps");

// Создаем дивизии
$div1 = new Division("Mechanized Division");
$div2 = new Division("Armor Division");
$div3 = new Division("Airborne Division");

// Создаем взводы
$squad1 = new Squad("Tank Squad");
$squad2 = new Squad("Infantry Squad");
$squad3 = new Squad("Recon Squad");

// Добавляем боевые единицы
$squad1->add(new Tank()); // 20
$squad1->add(new Tank()); // 20
$squad1->add(new Infantryman()); // 5

$squad2->add(new Infantryman()); // 5
$squad2->add(new Infantryman()); // 5
$squad2->add(new Archer()); // 3
$squad2->add(new Archer()); // 3

$squad3->add(new Cavalryman()); // 8
$squad3->add(new Helicopter()); // 25
// 94

// Собираем дивизии
$div1->add($squad1);
$div1->add($squad2);

$div2->add($squad3);

$div3->add(new Infantryman()); // 5
$div3->add(new Infantryman()); // 5
$div3->add(new Cavalryman()); // 8
// 112

// Собираем корпуса
$corps1->add($div1);
$corps1->add($div2);

$corps2->add($div3);

// Собираем армию
$army->add($corps1);
$army->add($corps2);

// Выводим информацию
echo "Army: " . $army->getName() . "\n"; // Army: Northern Army
echo "Total attack power: " . $army->attackPower() . "\n"; //Total attack power: 112
echo "Structure:\n" . $army . "\n";
// Northern Army (Children: Shock Corps (Children: Mechanized Division (Children: Tank Squad (Child...)))

// Проверяем вложенность N4 !
foreach ($army->getChildren() as $corps) {
    echo "Corps: " . $corps->getName() . " (Power: " . $corps->attackPower() . ")\n";
    // Corps: Shock Corps (Power: 94)...

    foreach ($corps->getChildren() as $division) {
        echo "  Division: " . $division->getName() . " (Power: " . $division->attackPower() . ")\n";
        // Mechanized Division (Power: 61)

        foreach ($division->getChildren() as $squad) {
            echo "    Squad: " . $squad->getName() . " (Power: " . $squad->attackPower() . ")\n";
            // Squad: Infantry Squad (Power: 16)

            foreach ($squad->getChildren() as $unit) {
                echo "      " . $unit . "\n";
                // Tank (Power: 20) ...
            }
        }
    }
}



echo "\n=== Transport Example v2 ===\n";

// Создаем транспорт
$transport = new Transport("Military Transport Plane", 100);

// Пытаемся загрузить
try {
    $transport->add(new Tank()); // 20 power
    $transport->add(new Tank()); // 20 power
    $transport->add(new Helicopter()); // 25 power
    $transport->add(new Infantryman()); // 5 power
    $transport->add(new Infantryman()); // 5 power
    $transport->add(new Archer()); // 3 power
    $transport->add(new Archer()); // 3 power
    $transport->add(new Cavalryman()); // 8 power - последний, сумма 69

    // Пытаемся добавить еще один танк (превысит лимит)
    $transport->add(new Tank()); // 20 power, сумма станет 89 + 20 = 109 > 100
    // Error: Transport overloaded! Cannot add: Tank
} catch (UnitException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Transport: " . $transport->getName() . "\n";
echo "Current load: " . $transport->getCurrentLoad() . "/" . $transport->getCapacity() . "\n";
echo "Total power: " . $transport->attackPower() . "\n";
echo "Units in transport:\n";
foreach ($transport->getChildren() as $unit) {
    echo " - " . $unit . "\n";
    // - Tank (Power: 20)  etc...
}



echo "\n=== FileSystem Example v3 ===\n";

// Создаем файловую систему
$root = new Directory("root");

$docs = new Directory("documents");
$docs->add(new File("report.pdf", 2048));
$docs->add(new File("notes.txt", 512));
$docs->add(new File("project.docx", 4096));

$images = new Directory("images");
$images->add(new File("photo.jpg", 102400));
$images->add(new File("screenshot.png", 51200));

$root->add($docs);
$root->add($images);
$root->add(new File("readme.md", 1024));

echo $root->print();
// D root (161280 bytes)
//  D documents (6656 bytes)
//    report.pdf (2048 bytes)
// ...



echo "\n=== Menu System Example v4 ===\n";

// Создаем меню
$menu = new MenuCategory("Main Menu");

$appetizers = new MenuCategory("Appetizers");
$appetizers->add(new SimpleMenuItem("Caesar Salad", 8.99, true));
$appetizers->add(new SimpleMenuItem("Buffalo Wings", 12.99, false));

$mainCourses = new MenuCategory("Main Courses");
$mainCourses->add(new SimpleMenuItem("Grilled Salmon", 22.99, false));
$mainCourses->add(new SimpleMenuItem("Vegetable Stir Fry", 16.99, true));
$mainCourses->add(new SimpleMenuItem("Ribeye Steak", 29.99, false));

$desserts = new MenuCategory("Desserts");
$desserts->add(new SimpleMenuItem("Cheesecake", 7.99, true));
$desserts->add(new SimpleMenuItem("Ice Cream", 5.99, true));

$menu->add($appetizers);
$menu->add($mainCourses);
$menu->add($desserts);

echo $menu->print();
// Main Menu:
// Appetizers:
//   • Caesar Salad (v) - $8.99
//   • Buffalo Wings  - $12.99
// Main Courses:
// ...

echo "\n=== Company Organization Example v5 ===\n";

// Создаем организационную структуру
$ceo = new Manager("John Smith", "CEO", 150000);

$cto = new Manager("Alice Johnson", "CTO", 120000);
$cfo = new Manager("Bob Brown", "CFO", 110000);
$cho = new Manager("Carol White", "CHRO", 100000);

$devManager = new Manager("Dave Wilson", "Development Manager", 90000);
$qaManager = new Manager("Eve Adams", "QA Manager", 80000);

$dev1 = new Developer("Frank Davis", "Senior Developer", 85000);
$dev2 = new Developer("Grace Miller", "Junior Developer", 60000);
$dev3 = new Developer("Henry Moore", "Developer", 75000);

$qa1 = new Developer("Irene Taylor", "QA Engineer", 70000);
$qa2 = new Developer("Jack Hall", "QA Engineer", 65000);

$ceo->add($cto);
$ceo->add($cfo);
$ceo->add($cho);

$cto->add($devManager);
$cto->add($qaManager);

$devManager->add($dev1);
$devManager->add($dev2);
$devManager->add($dev3);

$qaManager->add($qa1);
$qaManager->add($qa2);

echo $ceo->print();
// Выводим всех по иерархии от "CEO"
// Y John Smith (CEO, $150,000)
//  Y Alice Johnson (CTO, $120,000)
//    Y Dave Wilson (Development Manager, $90,000)
//      PC Frank Davis (Senior Developer, $85,000)
// ...
// т.е. у  CEO "ветки"[Y], есть дочерние "ветки"(Manager)[Y], а у них конечные "листья"(Developer)[PC]
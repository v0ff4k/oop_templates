<?php

declare(strict_types=1);


/**
 * Created by pSom.
 * User: 9r00+
 * at: 24.06.2026 - 15:07
 */

namespace Behavioral\Specification;

/**
 * Product Class - пример объекта, который проверяется спецификациями
 * Пример 1: Специфика товара
 */
class Product
{
    public function __construct(
        private string $name,
        private string $category,
        private string $brand,
        private float  $price,
        private bool   $inStock
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }
}

/**
 * User Class - пример объекта для валидации пользователей
 */
class User
{
    public function __construct(
        private string $username,
        private bool   $isActive,
        private bool   $isAdmin,
        private bool   $isEmailVerified
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function isEmailVerified(): bool
    {
        return $this->isEmailVerified;
    }
}

/**
 * Specification Interface - интерфейс спецификации
 */
interface Specification
{
    public function isSatisfiedBy(mixed $candidate): bool;

    public function and(Specification $specification): Specification;

    public function or(Specification $specification): Specification;

    public function not(): Specification;
}

/**
 * Abstract Specification - базовая реализация
 */
abstract class AbstractSpecification implements Specification
{
    public function and(Specification $specification): Specification
    {
        return new AndSpecification($this, $specification);
    }

    public function or(Specification $specification): Specification
    {
        return new OrSpecification($this, $specification);
    }

    public function not(): Specification
    {
        return new NotSpecification($this);
    }
}

/**
 * Concrete Specifications
 */
class PriceSpecification extends AbstractSpecification
{
    public function __construct(
        private float $minPrice,
        private float $maxPrice
    ) {
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Product) {
            return false;
        }

        $price = $candidate->getPrice();

        return ($price >= $this->minPrice && $price <= $this->maxPrice);
    }
}

class CategorySpecification extends AbstractSpecification
{
    /** @var string[] */
    private array $categories;

    public function __construct(string ...$categories)
    {
        $this->categories = $categories;
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Product) {
            return false;
        }

        return in_array($candidate->getCategory(), $this->categories);
    }
}

class BrandSpecification extends AbstractSpecification
{
    private string $brand;

    public function __construct(string $brand)
    {
        $this->brand = $brand;
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Product) {
            return false;
        }

        return $candidate->getBrand() === $this->brand;
    }
}

class InStockSpecification extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof Product) {
            return false;
        }

        return $candidate->isInStock();
    }
}

/**
 * Composite Specifications
 */
class AndSpecification extends AbstractSpecification
{
    /** @var Specification[] */
    private array $specifications;

    public function __construct(Specification ...$specifications)
    {
        $this->specifications = $specifications;
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($candidate)) {
                return false;
            }
        }

        return true;
    }
}

class OrSpecification extends AbstractSpecification
{
    /** @var Specification[] */
    private array $specifications;

    public function __construct(Specification ...$specifications)
    {
        $this->specifications = $specifications;
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        foreach ($this->specifications as $specification) {
            if ($specification->isSatisfiedBy($candidate)) {
                return true;
            }
        }

        return false;
    }
}

class NotSpecification extends AbstractSpecification
{
    private Specification $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return !$this->specification->isSatisfiedBy($candidate);
    }
}


/**
 * Пример 2: Валидация формы
 */
class FormFieldSpecification extends AbstractSpecification
{
    public function __construct(
        private string $fieldName,
        private string $errorMessage
    ) {
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!is_array($candidate)) {
            return false;
        }

        return isset($candidate[$this->fieldName]) && !empty($candidate[$this->fieldName]);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}

class EmailFieldSpecification extends FormFieldSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!parent::isSatisfiedBy($candidate)) {
            return false;
        }

        // $value = $candidate[$this->fieldName] ?? '';
        $value = '';
        if (property_exists($this, 'fieldName')) {
            $value = $candidate[$this->fieldName] ?? '';
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}

class MinLengthSpecification extends FormFieldSpecification
{
    private int $minLength;

    public function __construct(string $fieldName, int $minLength, string $errorMessage)
    {
        $this->minLength = $minLength;
        parent::__construct($fieldName, $errorMessage);
    }

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!parent::isSatisfiedBy($candidate)) {
            return false;
        }

        $value = $candidate[$this->fieldName] ?? '';

        return strlen($value) >= $this->minLength;
    }
}


/**
 * Пример 3: Поиск пользователей
 */
class UserSpecification extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!$candidate instanceof User) {
            return false;
        }

        return true;
    }
}

class ActiveUserSpecification extends UserSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!parent::isSatisfiedBy($candidate)) {
            return false;
        }

        return $candidate->isActive();
    }
}

class AdminUserSpecification extends UserSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!parent::isSatisfiedBy($candidate)) {
            return false;
        }

        return $candidate->isAdmin();
    }
}

class EmailVerifiedUserSpecification extends UserSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (!parent::isSatisfiedBy($candidate)) {
            return false;
        }

        return $candidate->isEmailVerified();
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Specification Interface - определяет методы isSatisfiedBy(), and(), or(), not()
 *    Abstract Specification - базовая реализация комбинаторов
 *    Concrete Specifications - реализуют конкретные правила проверки
 *    Composite Specifications - комбинируют несколько спецификаций
 *    Client - создает спецификации и проверяет объекты
 *
 * Преимущества:
 *    Повторное использование - спецификации можно комбинировать
 *    Разделение ответственности - бизнес-правила отделены от данных
 *    Гибкость - легко добавлять новые правила
 *    Тестируемость - каждая спецификация тестируется отдельно
 *    Читаемость - сложные правила выражаются композицией
 * Недостатки:
 *    Производительность - много мелких объектов
 *    Сложность - может быть избыточным для простых случаев
 *    Наследование - требует создания множества классов
 *    Переиспользование - спецификации привязаны к конкретным классам
 *
 * Где используется в фреймворках:
 * 1. Laravel's Eloquent Scopes
 *    class PriceScope
 *    {
 *        public function apply(Builder $query, Model $model)
 *        {
 *            return $query->whereBetween('price', [$this->min, $this->max]);
 *        }
 *    }
 *    // Использование
 *    Product::priceBetween(100, 500)->get();
 * 2. Symfony's Validator Constraints(annotations in SF v2...4)
 *    use Symfony\Component\Validator\Constraints as Assert;
 *    class Product
 *    {
 *        / * *
 *         * @Assert\NotBlank
 *         * @Assert\Range(min=0, max=10000)
 *         * /
 *        private float $price;
 *        / * *
 *         * @Assert\Choice({"Electronics", "Clothing", "Home"})
 *         * /
 *        private string $category;
 *    }
 * 3. Doctrine's Query Builder
 *    $expr = $queryBuilder->expr();
 *    $priceSpec = $expr->between('p.price', ':min', ':max');
 *    $categorySpec = $expr->in('p.category', ':categories');
 *    $query = $queryBuilder->select('p')
 *        ->from('Product', 'p')
 *        ->where($priceSpec)
 *        ->andWhere($categorySpec)
 *        ->getQuery();
 * 4. Laravel's Form Requests
 *    class StoreProductRequest extends FormRequest
 *    {
 *        public function rules()
 *        {
 *            return [
 *                'name' => ['required', 'string', 'max:255'],
 *                'price' => ['required', 'numeric', 'between:0,10000'],
 *                'category' => ['required', 'in:Electronics,Clothing,Home'],
 *            ];
 *        }
 *    }
 * 5. Laravel's Policy Filters
 *    class ProductPolicy
 *    {
 *        public function before($user, $ability)
 *        {
 *            if ($user->isAdmin()) {
 *                return true;
 *            }
 *        }
 *        public function view(User $user, Product $product)
 *        {
 *            return $user->id === $product->user_id;
 *        }
 *    }
 * 6. Laravel's Query Filters
 *    class ProductFilter
 *    {
 *        public function filter(Builder $query, array $filters)
 *        {
 *            if (isset($filters['min_price'])) {
 *                $query->where('price', '>=', $filters['min_price']);
 *            }
 *            if (isset($filters['category'])) {
 *                $query->where('category', $filters['category']);
 *            }
 *            return $query;
 *        }
 *    }
 *    // Использование
 *    Product::filter(new ProductFilter(), $request->all())->get();
 * 7. Laravel's Search Schemas
 *    class ProductSearch
 *    {
 *        public function toSearchableArray()
 *        {
 *            return [
 *                'name' => $this->name,
 *                'price' => $this->price,
 *                'category' => $this->category,
 *            ];
 *        }
 *    }
 *    // Algolia search
 *    Product::search('iphone')->get();
 *
 * Когда полезен:
 *    Фильтрация данных - сложные условия выборки
 *    Валидация - бизнес-правила
 *    Поиск - поиск по критериям
 *    Авторизация - политики доступа
 *    Права доступа - RBAC/ACL
 *    Правила бизнеса - сложные условия
 */


echo "=== Product Specification Example ===\n";

// Создаем продукты
$products = [
    new Product('iPhone 15', 'Electronics', 'Apple', 999, true),
    new Product('MacBook Pro', 'Electronics', 'Apple', 1999, true),
    new Product('Samsung Galaxy S24', 'Electronics', 'Samsung', 899, true),
    new Product('T-Shirt', 'Clothing', 'Nike', 29, false),
    new Product('Jeans', 'Clothing', 'Levi\'s', 89, true),
    new Product('Coffee Maker', 'Home', 'Breville', 150, false),
];

// Создаем спецификации
$appleProducts = new BrandSpecification('Apple');
$from = 500;
$to = 2000;
$expensiveProducts = new PriceSpecification($from, $to);
$electronics = new CategorySpecification('Electronics');
$inStock = new InStockSpecification();

// Комбинируем спецификации
$appleExpensiveElectronics = $appleProducts
    ->and($expensiveProducts)
    ->and($electronics);

$appleInStockElectronics = $appleProducts
    ->and($inStock)
    ->and($electronics);

// Фильтрация
$filtered = array_filter(
    $products,
    fn ($product) => $appleExpensiveElectronics->isSatisfiedBy($product)
);
echo 'Apple products priced $'.$from.'-$'.$to.' in Electronics:.'."\n"; // Apple products priced $500-$2000 in Electronics:
foreach ($filtered as $product) {
    echo " - {$product->getName()}: \${$product->getPrice()}\n";
}
// - iPhone 15: $999
// - MacBook Pro: $1999

echo "\nApple products in stock (Electronics):\n";
$filtered = array_filter($products, fn ($product) => $appleInStockElectronics->isSatisfiedBy($product));
foreach ($filtered as $product) {
    echo " - {$product->getName()}: \${$product->getPrice()}\n";
}
// - iPhone 15: $999
// - MacBook Pro: $1999

echo "\nNon-Apple products under $100:\n";
$nonApple = new BrandSpecification('Apple')->not();
$under100 = new PriceSpecification(0, 100);
$nonAppleUnder100 = $nonApple->and($under100);
$filtered = array_filter($products, fn ($product) => $nonAppleUnder100->isSatisfiedBy($product));
foreach ($filtered as $product) {
    echo " - {$product->getName()}: \${$product->getPrice()}\n";
}
// - T-Shirt: $29
// - Jeans: $89



echo "\n=== Form Validation Example ===\n";

$form = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
    'age' => '25'
];

// Создаем спецификации валидации
$usernameSpec = new FormFieldSpecification('username', 'Username is required');
$emailSpec = (new EmailFieldSpecification('email', 'Invalid email'))
    ->and(
        new FormFieldSpecification('email', 'Email is required')
    );
$passwordSpec = (new MinLengthSpecification('password', 8, 'Password must be at least 8 characters'))
    ->and(new FormFieldSpecification('password', 'Password is required'));

// Комбинируем все спецификации
$allSpecs = $usernameSpec->and($emailSpec)->and($passwordSpec);

if ($allSpecs->isSatisfiedBy($form)) {
    echo "Form is valid!\n";
} else {
    echo "Form has errors:\n";
    // Для простоты не показываем конкретные ошибки
}
// так как у нас нет "key in",  без property_exists() выведет Warning
//Warning: Undefined property: ...EmailFieldSpecification::$fieldName in ....php on line 302
//Form has errors:


echo "\n=== User Specification Example ===\n";

$users = [
    new User('alice', true, false, false),
    new User('bob', true, true, false),
    new User('charlie', false, false, true),
    new User('david', true, true, true),
];

// Админы, которые активны и подтвердили email
$adminSpec = new AdminUserSpecification();
$activeSpec = new ActiveUserSpecification();
$emailVerifiedSpec = new EmailVerifiedUserSpecification();

$adminActiveVerified = $adminSpec->and($activeSpec)->and($emailVerifiedSpec);

$filtered = array_filter($users, fn ($user) => $adminActiveVerified->isSatisfiedBy($user));
echo "Active admins with verified email:\n";
foreach ($filtered as $user) {
    echo " - {$user->getUsername()}\n";
}
// Active admins with verified email:
// - david


// Активные пользователи, которые НЕ админы
$activeNonAdmins = $activeSpec->and($adminSpec->not());
$filtered = array_filter($users, fn ($user) => $activeNonAdmins->isSatisfiedBy($user));
echo "\nActive non-admin users:\n";
foreach ($filtered as $user) {
    echo " - {$user->getUsername()}\n";
}
// Active non-admin users:
// - alice

<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 04.08.2026 - 17:57
 */

namespace Creational\Builder;

/**
 * Product - сложный объект, который создается
 */
class Pizza
{
    private string $dough;
    private string $sauce;
    private array $toppings = [];
    private bool $cheese;
    private bool $pepperoni;
    private bool $olives;
    private bool $mushrooms;
    private int $size;

    public function setDough(string $dough): void
    {
        $this->dough = $dough;
    }

    public function setSauce(string $sauce): void
    {
        $this->sauce = $sauce;
    }

    public function addTopping(string $topping): void
    {
        $this->toppings[] = $topping;
    }

    public function setCheese(bool $cheese): void
    {
        $this->cheese = $cheese;
    }

    public function setPepperoni(bool $pepperoni): void
    {
        $this->pepperoni = $pepperoni;
    }

    public function setOlives(bool $olives): void
    {
        $this->olives = $olives;
    }

    public function setMushrooms(bool $mushrooms): void
    {
        $this->mushrooms = $mushrooms;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getDescription(): string
    {
        $description = "Pizza (Size: {$this->size})\n";
        $description .= "Dough: {$this->dough}\n";
        $description .= "Sauce: {$this->sauce}\n";
        $description .= 'Cheese: ' . ($this->cheese ? 'Yes' : 'No') . "\n";
        $description .= 'Pepperoni: ' . ($this->pepperoni ? 'Yes' : 'No') . "\n";
        $description .= 'Olives: ' . ($this->olives ? 'Yes' : 'No') . "\n";
        $description .= 'Mushrooms: ' . ($this->mushrooms ? 'Yes' : 'No') . "\n";
        $description .= 'Toppings: ' . implode(', ', $this->toppings) . "\n";

        return $description;
    }
}

/**
 * Builder - интерфейс для построения пиццы
 */
interface PizzaBuilder
{
    public function buildDough(): void;
    public function buildSauce(): void;
    public function buildCheese(): void;
    public function buildToppings(): void;
    public function build(): Pizza;
}

/**
 * Concrete Builder 1 - Вегетарианская пицца
 */
class VegetarianPizzaBuilder implements PizzaBuilder
{
    private Pizza $pizza;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->pizza = new Pizza();
    }

    public function buildDough(): void
    {
        $this->pizza->setDough('Whole wheat');
    }

    public function buildSauce(): void
    {
        $this->pizza->setSauce('Tomato basil');
    }

    public function buildCheese(): void
    {
        $this->pizza->setCheese(true);
    }

    public function buildToppings(): void
    {
        $this->pizza->addTopping('Mushrooms');
        $this->pizza->addTopping('Olives');
        $this->pizza->addTopping('Bell peppers');
        $this->pizza->addTopping('Onions');
        $this->pizza->setPepperoni(false);
        $this->pizza->setOlives(true);
        $this->pizza->setMushrooms(true);
    }

    public function build(): Pizza
    {
        $pizza = $this->pizza;
        $this->reset();
        return $pizza;
    }
}

/**
 * Concrete Builder 2 - Пепперони пицца
 */
class PepperoniPizzaBuilder implements PizzaBuilder
{
    private Pizza $pizza;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->pizza = new Pizza();
    }

    public function buildDough(): void
    {
        $this->pizza->setDough('Classic white');
    }

    public function buildSauce(): void
    {
        $this->pizza->setSauce('Spicy tomato');
    }

    public function buildCheese(): void
    {
        $this->pizza->setCheese(true);
    }

    public function buildToppings(): void
    {
        $this->pizza->addTopping('Pepperoni');
        $this->pizza->addTopping('Extra cheese');
        $this->pizza->setPepperoni(true);
        $this->pizza->setOlives(false);
        $this->pizza->setMushrooms(false);
    }

    public function build(): Pizza
    {
        $pizza = $this->pizza;
        $this->reset();
        return $pizza;
    }
}

/**
 * Director - управляет процессом построения
 */
class PizzaDirector
{
    private PizzaBuilder $builder;

    public function setBuilder(PizzaBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildSmall(): void
    {
        $this->builder->build();
        $this->builder->pizza->setSize(8);
    }

    public function buildMedium(): void
    {
        $this->builder->build();
        $this->builder->pizza->setSize(12);
    }

    public function buildLarge(): void
    {
        $this->builder->build();
        $this->builder->pizza->setSize(16);
    }

    public function getPizza(): Pizza
    {
        return $this->builder->build();
    }
}

/**
 * Пример 2: Builder для SQL запросов
 */
class QueryBuilder
{
    private string $table;
    private array $columns = [];
    private array $conditions = [];
    private array $bindings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private string $orderBy = '';
    private string $direction = 'ASC';

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function where(string $column, $value, string $operator = '='): self
    {
        $this->conditions[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->conditions[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereLike(string $column, string $pattern): self
    {
        $this->conditions[] = "$column LIKE ?";
        $this->bindings[] = $pattern;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = $column;
        $this->direction = $direction;
        return $this;
    }

    public function getQuery(): string
    {
        $query = 'SELECT ' . implode(', ', $this->columns);
        $query .= ' FROM ' . $this->table;

        if (!empty($this->conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        if ($this->orderBy) {
            $query .= " ORDER BY {$this->orderBy} {$this->direction}";
        }

        if ($this->limit !== null) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $query .= ' OFFSET ' . $this->offset;
        }

        return $query;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function reset(): self
    {
        $this->table = '';
        $this->columns = [];
        $this->conditions = [];
        $this->bindings = [];
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = '';
        $this->direction = 'ASC';
        return $this;
    }
}

/**
 * Пример 3: Builder для конфигураций
 */
class ConfigBuilder
{
    private array $config = [];

    public function set(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function setArray(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function merge(ConfigBuilder $builder): self
    {
        $this->config = array_merge($this->config, $builder->getConfig());
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function remove(string $key): self
    {
        unset($this->config[$key]);
        return $this;
    }

    public function clear(): self
    {
        $this->config = [];
        return $this;
    }
}

/**
 * Пример 4: Builder для HTTP запросов
 */
class HttpRequestBuilder
{
    private string $method = 'GET';
    private string $url = '';
    private array $headers = [];
    private array $query = [];
    private array $body = [];
    private int $timeout = 30;
    private bool $verifySsl = true;

    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function query(array $query): self
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }

    public function body(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function verifySsl(bool $verify): self
    {
        $this->verifySsl = $verify;
        return $this;
    }

    public function build(): array
    {
        $url = $this->url;

        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query);
        }

        return [
            'method' => $this->method,
            'url' => $url,
            'headers' => $this->headers,
            'body' => $this->body,
            'timeout' => $this->timeout,
            'verify_ssl' => $this->verifySsl,
        ];
    }

    public function send(): array
    {
        $request = $this->build();

        $ch = curl_init($request['url']);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $request['timeout'],
            CURLOPT_SSL_VERIFYPEER => $request['verify_ssl'],
            CURLOPT_CUSTOMREQUEST => $request['method'],
        ]);

        if (!empty($request['headers'])) {
            $headerString = [];
            foreach ($request['headers'] as $name => $value) {
                $headerString[] = "$name: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerString);
        }

        if (!empty($request['body']) && in_array($request['method'], ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request['headers']['Content-Type'] ?? 'application/json';

            if ($contentType === 'application/json') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request['body']));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request['body']));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
            'headers' => $this->parseHeaders($response),
        ];
    }

    private function parseHeaders(string $response): array
    {
        // Упрощенный парсинг
        return [
            'http_code' => 200,
            'content_type' => 'application/json',
        ];
    }

    public function reset(): self
    {
        $this->method = 'GET';
        $this->url = '';
        $this->headers = [];
        $this->query = [];
        $this->body = [];
        $this->timeout = 30;
        $this->verifySsl = true;
        return $this;
    }
}

/**
 * Пример 5: Builder для Email сообщений
 */
class EmailBuilder
{
    private string $from = '';
    private array $to = [];
    private array $cc = [];
    private array $bcc = [];
    private string $subject = '';
    private string $body = '';
    private array $attachments = [];
    private string $contentType = 'text/plain';
    private string $charset = 'UTF-8';

    public function from(string $email, string $name = ''): self
    {
        $this->from = $name ? "$name <$email>" : $email;
        return $this;
    }

    public function to(string $email, string $name = ''): self
    {
        $this->to[] = $name ? "$name <$email>" : $email;
        return $this;
    }

    public function cc(string $email, string $name = ''): self
    {
        $this->cc[] = $name ? "$name <$email>" : $email;
        return $this;
    }

    public function bcc(string $email, string $name = ''): self
    {
        $this->bcc[] = $name ? "$name <$email>" : $email;
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function attachment(string $path, string $type = 'application/octet-stream'): self
    {
        $this->attachments[] = [
            'path' => $path,
            'type' => $type,
            'filename' => basename($path),
        ];
        return $this;
    }

    public function contentType(string $type): self
    {
        $this->contentType = $type;
        return $this;
    }

    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function build(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'subject' => $this->subject,
            'body' => $this->body,
            'attachments' => $this->attachments,
            'headers' => $this->buildHeaders(),
        ];
    }

    private function buildHeaders(): array
    {
        $headers = [
            'From' => $this->from,
            'Content-Type' => $this->contentType . '; charset=' . $this->charset,
            'MIME-Version' => '1.0',
        ];

        if (!empty($this->to)) {
            $headers['To'] = implode(', ', $this->to);
        }

        if (!empty($this->cc)) {
            $headers['Cc'] = implode(', ', $this->cc);
        }

        if (!empty($this->bcc)) {
            $headers['Bcc'] = implode(', ', $this->bcc);
        }

        return $headers;
    }

    public function reset(): self
    {
        $this->from = '';
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->subject = '';
        $this->body = '';
        $this->attachments = [];
        $this->contentType = 'text/plain';
        $this->charset = 'UTF-8';
        return $this;
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Builder — интерфейс для создания частей объекта
 *    ConcreteBuilder — реализация Builder, создающая конкретные части
 *    Director — управляет процессом конструирования (опционально)
 *    Product — сложный объект, который создается
 *    Клиент — использует Director и Builder для создания объекта
 *
 * Как это работает:
 *    Builder определяет интерфейс для создания частей объекта
 *    ConcreteBuilder реализует этот интерфейс, создавая конкретные части
 *    Director управляет процессом конструирования (опционально, но часто используется)
 *    Product — сложный объект, который создается
 *    Клиент использует Director и Builder для создания объекта
 *
 * Преимущества:
 *    Читаемость — код легко читать и понимать
 *    Гибкость — можно легко добавлять новые параметры
 *    Контроль процесса — можно контролировать каждый шаг конструирования
 *    Изоляция — отделение конструирования от представления
 *    Поддержка необязательных параметров — легко добавлять опциональные параметры
 *    Повторное использование — один и тот же процесс конструирования может создавать разные представления
 * Недостатки:
 *    Избыточность — создается много классов для разных представлений
 *    Сложность — добавляет дополнительный уровень абстракции
 *    Производительность — может быть медленнее, чем прямое создание объектов
 *    Переизбыточность — иногда проще использовать обычные конструкторы с параметрами по умолчанию
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Query Builder
 *    // Laravel Query Builder - классический пример Builder pattern
 *    $users = DB::table('users')
 *        ->select('name', 'email')
 *        ->where('active', 1)
 *        ->where('role', 'admin')
 *        ->orderBy('created_at', 'desc')
 *        ->limit(10)
 *        ->get();
 *    // Или с join
 *    $orders = DB::table('orders')
 *        ->join('users', 'orders.user_id', '=', 'users.id')
 *        ->select('orders.*', 'users.name as user_name')
 *        ->where('orders.status', 'completed')
 *        ->get();
 * 2. Laravel's Eloquent Builder
 *    // Laravel Eloquent Builder
 *    $user = User::where('active', 1)
 *        ->whereHas('posts', function ($query) {
 *            $query->where('status', 'published');
 *        })
 *        ->with(['posts' => function ($query) {
 *            $query->orderBy('created_at', 'desc');
 *        }])
 *        ->first();
 *    // Или для создания
 *    $user = new User();
 *    $user->name = 'John';
 *    $user->email = 'john@example.com';
 *    $user->save();
 *    // Или с create
 *    $user = User::create([
 *        'name' => 'John',
 *        'email' => 'john@example.com',
 *    ]);
 * 3. Laravel's Mail Builder
 *    // Laravel Mail Builder
 *    Mail::to($request->user())
 *        ->cc($moreUsers)
 *        ->bcc($evenMoreUsers)
 *        ->send(new OrderShipped($order));
 * 4. Laravel's Validator Builder
 *    // Laravel Validator Builder
 *    $validator = Validator::make($request->all(), [
 *        'title' => 'required|string|max:255',
 *        'body' => 'required|string',
 *    ]);
 *    // Или с кастомными сообщениями
 *    $validator = Validator::make($request->all(), [
 *        'email' => 'required|email',
 *    ], [
 *        'email.required' => 'We need to know your email address!',
 *    ]);
 * 5. Laravel's Form Builder
 *    // Laravel Form Builder
 *    echo Form::open(['url' => 'foo/bar']);
 *    echo Form::label('email', 'E-Mail Address');
 *    echo Form::email('email');
 *    echo Form::close();
 * 6. Symfony's Form Builder
 *    // Symfony Form Builder
 *    $form = $this->createFormBuilder($task)
 *        ->add('task', TextType::class)
 *        ->add('dueDate', DateType::class)
 *        ->add('save', SubmitType::class, ['label' => 'Create Task'])
 *        ->getForm();
 * 7. Symfony's QueryBuilder
 *    // Symfony Doctrine QueryBuilder
 *    $query = $entityManager->createQueryBuilder()
 *        ->select('u')
 *        ->from('User', 'u')
 *        ->where('u.email = ?1')
 *        ->setParameter(1, 'john@example.com')
 *        ->getQuery();
 *    $user = $query->getSingleResult();
 * 8. Yii Framework's QueryBuilder
 *    // Yii QueryBuilder
 *    $query = (new \yii\db\Query())
 *        ->select(['id', 'email'])
 *        ->from('{{%user}}')
 *        ->where(['status' => 1])
 *        ->limit(10);
 *    $rows = $query->all();
 * 9. CakePHP's QueryBuilder
 *    // CakePHP QueryBuilder
 *    $query = $this->Users->find()
 *        ->select(['id', 'email'])
 *        ->where(['status' => 1])
 *        ->limit(10);
 * 10. Nette's SqlBuilder
 *    // Nette SqlBuilder
 *    $sql = "SELECT * FROM users WHERE active = 1";
 *    $result = $connection->query($sql);
 * 11. Laravel's Notification Builder
 *    // Laravel Notification Builder
 *    $notification = (new OrderShipped($order))
 *        ->locale('en');
 * 12. Laravel's Event Builder
 *    // Laravel Event Builder
 *    Event::listen(OrderShipped::class, function (OrderShipped $event) {
 *        //
 *    });
 *
 * Когда полезен:
 *    Сложные объекты — когда объект имеет много частей или опциональных параметров
 *    Пошаговое конструирование — когда нужно контролировать каждый шаг создания
 *    Разные представления — когда один и тот же процесс конструирования создает разные представления
 *    Читаемость кода — когда конструктор с множеством параметров становится нечитаемым
 *    Тестирование — легче тестировать отдельные шаги конструирования
 *
 * Разница между Builder и других порождающих паттернов:
 *
 *    Builder = Пошаговое конструирование +Отделяет конструирование от представления +Для сложных объектов
 * когда нужно создать сложный объект со множеством параметров, особенно если часть параметров опциональна или объект может иметь разные вариации.
 *
 *    Factory Method = Создание одного объекта +Использует наследование +Для простых объектов
 * когда заранее неизвестно, объекты какого конкретно класса вам понадобятся, но базовый класс должен делегировать создание подклассам.
 *
 *    Abstract Factory = Создание семейства объектов +Использует композицию +Для связанных объектов
 * когда нужно создавать семейства взаимосвязанных объектов, не привязываясь к их конкретным классам.
 *
 *    Prototype = Клонирование объекта +Использует клонирование +Для клонирования
 * когда создание нового объекта дороже или сложнее, чем копирование уже существующего.
 *
 *    Singleton = Один экземпляр +Использует глобальный доступ +Для единственного объекта
 * когда в приложении должен существовать ровно один экземпляр класса, и к нему нужен глобальный доступ.
 *
 */

try {
    echo "=== Builder Pattern Example ===\n\n";


    // Пример 1: Pizza Builder
    echo "=== Pizza Builder ===\n";

    $director = new PizzaDirector();

    $vegetarianBuilder = new VegetarianPizzaBuilder();
    $director->setBuilder($vegetarianBuilder);
    $director->buildMedium();
    $vegetarianPizza = $director->getPizza();

    $pepperoniBuilder = new PepperoniPizzaBuilder();
    $director->setBuilder($pepperoniBuilder);
    $director->buildLarge();
    $pepperoniPizza = $director->getPizza();

    echo "Vegetarian Pizza:\n" . $vegetarianPizza->getDescription() . "\n";
    echo "Pepperoni Pizza:\n" . $pepperoniPizza->getDescription() . "\n\n";


    // Пример 2: Query Builder
    echo "=== Query Builder ===\n";

    $queryBuilder = new QueryBuilder();

    $query = $queryBuilder
        ->select(['id', 'name', 'email'])
        ->from('users')
        ->where('status', 'active')
        ->where('role', 'admin', '=')
        ->whereIn('id', [1, 2, 3, 4, 5])
        ->whereLike('name', '%john%')
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->offset(0)
        ->getQuery();

    echo "SQL Query: $query\n";
    echo 'Bindings: ' . json_encode($queryBuilder->getBindings()) . "\n\n";


    // Пример 3: Config Builder
    echo "=== Config Builder ===\n";

    $configBuilder = new ConfigBuilder();

    $config = $configBuilder
        ->set('app.name', 'My Application')
        ->set('app.version', '1.0.0')
        ->set('database.host', 'localhost')
        ->set('database.port', 3306)
        ->set('cache.enabled', true)
        ->setArray([
            'debug' => true,
            'timezone' => 'UTC',
            'locale' => 'en_US',
        ])
        ->getConfig();

    echo "Configuration:\n";
    print_r($config);
    echo "\n";


    // Пример 4: HTTP Request Builder
    echo "=== HTTP Request Builder ===\n";

    $requestBuilder = new HttpRequestBuilder();

    $request = $requestBuilder
        ->method('POST')
        ->url('https://jsonplaceholder.typicode.com/posts')
        ->header('Content-Type', 'application/json')
        ->header('Accept', 'application/json')
        ->query(['page' => 1, 'limit' => 10])
        ->body([
            'title' => 'Builder Interface',
            'body' => 'This is an example of builder pattern',
            'userId' => 1,
        ])
        ->timeout(10)
        ->build();

    echo "HTTP Request:\n";
    echo "Method: {$request['method']}\n";
    echo "URL: {$request['url']}\n";
    echo 'Headers: ' . json_encode($request['headers']) . "\n";
    echo 'Body: ' . json_encode($request['body']) . "\n\n";

    // Пример 5: Email Builder
    echo "=== Email Builder ===\n";

    $emailBuilder = new EmailBuilder();

    $email = $emailBuilder
        ->from('noreply@example.com', 'System')
        ->to('john@example.com', 'John Doe')
        ->cc('manager@example.com', 'Manager')
        ->bcc('admin@example.com', 'Admin')
        ->subject('Welcome to Our Service')
        ->body("Hello John,\n\nWelcome to our service!\n\nBest regards,\nTeam")
        ->attachment('/tmp/welcome.pdf')
        ->contentType('text/html')
        ->build();

    echo "Email to send:\n";
    echo "From: {$email['from']}\n";
    echo 'To: ' . implode(', ', $email['to']) . "\n";
    echo "Subject: {$email['subject']}\n";
    echo 'Body length: ' . strlen($email['body']) . " characters\n";
    echo 'Attachments: ' . count($email['attachments']) . "\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/* Для PHP 8.4    *************************************

// Использование атрибутов для автоматической генерации Builder
#[Builder]
class Pizza
{
    private string $dough;
    private string $sauce;
    private array $toppings = [];
    private bool $cheese;
    private bool $pepperoni;
    private bool $olives;
    private bool $mushrooms;
    private int $size;

    // Геттеры и сеттеры
}

// Генерация Builder через атрибуты
class BuilderGenerator
{
    public function generate(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $builder = new class($class) implements PizzaBuilder {
            private $target;

            public function __construct(string $class)
            {
                $this->target = new $class();
            }

            public function __call($name, $arguments)
            {
                $property = lcfirst(substr($name, 3));
                if (property_exists($this->target, $property)) {
                    $this->target->$property = $arguments[0];
                }
                return $this;
            }

            public function build(): object
            {
                return $this->target;
            }
        };

        return $builder;
    }
}

// Pattern matching для автоматического создания Builder
public function getBuilder(string $type): object
{
    return match ($type) {
        'pizza' => match ($style) {
            'vegetarian' => new VegetarianPizzaBuilder(),
            'pepperoni' => new PepperoniPizzaBuilder(),
            'hawaiian' => new HawaiianPizzaBuilder(),
            default => throw new InvalidArgumentException("Unknown pizza style"),
        },
        'query' => new QueryBuilder(),
        'config' => new ConfigBuilder(),
        'http' => new HttpRequestBuilder(),
        'email' => new EmailBuilder(),
        default => throw new InvalidArgumentException("Unknown builder type"),
    };
}

// Enum для типов Builder
enum BuilderType: string
{
    case PIZZA = 'pizza';
    case QUERY = 'query';
    case CONFIG = 'config';
    case HTTP = 'http';
    case EMAIL = 'email';
    case FORM = 'form';
    case NOTIFICATION = 'notification';
    case EVENT = 'event';
    case VALIDATOR = 'validator';
}

class BuilderFactory
{
    public function create(BuilderType $type, array $config = []): object
    {
        return match ($type) {
            BuilderType::PIZZA => match ($config['style'] ?? 'vegetarian') {
                'vegetarian' => new VegetarianPizzaBuilder(),
                'pepperoni' => new PepperoniPizzaBuilder(),
                'hawaiian' => new HawaiianPizzaBuilder(),
                default => throw new InvalidArgumentException("Unknown pizza style"),
            },
            BuilderType::QUERY => new QueryBuilder(),
            BuilderType::CONFIG => new ConfigBuilder(),
            BuilderType::HTTP => new HttpRequestBuilder(),
            BuilderType::EMAIL => new EmailBuilder(),
            BuilderType::FORM => new FormBuilder(),
            BuilderType::NOTIFICATION => new NotificationBuilder(),
            BuilderType::EVENT => new EventBuilder(),
            BuilderType::VALIDATOR => new ValidatorBuilder(),
        };
    }
}

**************************************  */

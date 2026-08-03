<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 03.08.2026 - 12:23
 */

namespace Structural\FluentInterface;

use Exception;

/**
 * Пример 1: Fluent Builder для SQL запросов
 */
class SqlQueryBuilder
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
 * Пример 2: Fluent Email Builder
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
 * Пример 3: Fluent HTTP Request Builder
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
 * Пример 4: Fluent Configuration Builder
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
 * Пример 5: Fluent Validation Builder
 */
class ValidationBuilder
{
    private array $rules = [];
    private array $messages = [];
    private array $data = [];

    public function rule(string $field, string $rule, mixed $parameters = null): self
    {
        $this->rules[$field][] = [
            'rule' => $rule,
            'parameters' => $parameters,
        ];

        return $this;
    }

    public function required(string $field): self
    {
        $this->rules[$field][] = ['rule' => 'required'];

        return $this;
    }

    public function email(string $field): self
    {
        $this->rules[$field][] = ['rule' => 'email'];

        return $this;
    }

    public function min(string $field, int $min): self
    {
        $this->rules[$field][] = ['rule' => 'min', 'parameters' => $min];

        return $this;
    }

    public function max(string $field, int $max): self
    {
        $this->rules[$field][] = ['rule' => 'max', 'parameters' => $max];

        return $this;
    }

    public function between(string $field, int $min, int $max): self
    {
        $this->rules[$field][] = ['rule' => 'between', 'parameters' => [$min, $max]];

        return $this;
    }

    public function numeric(string $field): self
    {
        $this->rules[$field][] = ['rule' => 'numeric'];

        return $this;
    }

    public function message(string $field, string $message): self
    {
        $this->messages[$field] = $message;

        return $this;
    }

    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function validate(): ValidationResult
    {
        $errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            foreach ($fieldRules as $ruleInfo) {
                $rule = $ruleInfo['rule'];
                $parameters = $ruleInfo['parameters'] ?? null;

                $value = $this->data[$field] ?? null;

                if (!$this->checkRule($value, $rule, $parameters)) {
                    $errors[$field] = $this->messages[$field] ?? $this->getDefaultMessage($rule, $field, $parameters);
                    break; // Прерываем на первую ошибку в поле
                }
            }
        }

        return new ValidationResult(!$errors, $errors);
    }

    private function checkRule(mixed $value, string $rule, mixed $parameters): bool
    {
        return match ($rule) {
            'required' => $value !== null && $value !== '',
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => is_numeric($value) && $value >= $parameters,
            'max' => is_numeric($value) && $value <= $parameters,
            'between' => is_numeric($value) && $value >= $parameters[0] && $value <= $parameters[1],
            'numeric' => is_numeric($value),
            default => true,
        };
    }

    private function getDefaultMessage(string $rule, string $field, mixed $parameters): string
    {
        return match ($rule) {
            'required' => "$field is required",
            'email' => "$field must be a valid email address",
            'min' => "$field must be at least $parameters",
            'max' => "$field must not exceed $parameters",
            'between' => "$field must be between {$parameters[0]} and {$parameters[1]}",
            'numeric' => "$field must be numeric",
            default => "Validation failed for $field",
        };
    }

    public function reset(): self
    {
        $this->rules = [];
        $this->messages = [];
        $this->data = [];

        return $this;
    }
}

class ValidationResult
{
    public function __construct(
        public bool  $success,
        public array $errors = []
    ) {
    }
}

/**
 * Пример использования
 *
 * Основная идея
 *    Методы возвращают $this — каждый метод объекта возвращает сам объект, позволяя вызывать следующий метод
 *    Цепочка вызовов — методы вызываются одна за другой, создавая "плавный" интерфейс
 *    Читаемость — код выглядит как предложение на английском языке
 *
 * Аналог: простой Сеттер  ->setVar1(...)->setVar2(...)->...
 *
 * Как это работает:
 *    Методы возвращают $this — каждый метод объекта возвращает сам объект
 *    Цепочка вызовов — методы вызываются одна за другой
 *    Читаемость — код выглядит как естественное предложение на английском
 *
 * Преимущества:
 *    Читаемость — код легко читать и понимать
 *    Выразительность — можно создавать сложные конфигурации простым синтаксисом
 *    Удобство — меньше кода, чем с отдельными вызовами методов
 *    Гибкость — можно легко добавлять новые методы без изменения существующего кода
 *    Поддержка IDE — автодополнение работает лучше с цепочками методов
 *
 * Недостатки:
 *    Производительность — создание промежуточных объектов (хотя в PHP это обычно незначительно)
 *    Отладка — сложнее отлаживать цепочки методов
 *    Ошибки в цепочке — если один метод вернет неправильный тип, вся цепочка сломается
 *    Избыточность — иногда проще использовать обычные методы
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Query Builder
 *    // Laravel Query Builder - классический пример Fluent Interface
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
 * 2. Laravel's Eloquent
 *    // Eloquent также использует Fluent Interface
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
 * 3. Laravel's Mail
 *    // Mail facade с Fluent Interface
 *    Mail::to($request->user())
 *        ->cc($moreUsers)
 *        ->bcc($evenMoreUsers)
 *        ->send(new OrderShipped($order));
 * 4. Laravel's Validator
 *    // Validator с Fluent Interface
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
 * 5. Laravel's Collection
 *    // Collection методы возвращают новые коллекции
 *    $collection = collect([1, 2, 3, 4, 5])
 *        ->map(function ($x) { return $x * 2; })
 *        ->filter(function ($x) { return $x > 5; })
 *        ->sort()
 *        ->values()
 *        ->all();
 *    // Результат: [6, 8, 10]
 * 6. Laravel's Str и Arr
 *    // Str helper
 *    $result = Str::of('Hello World')
 *        ->ucfirst()
 *        ->replaceFirst('Hello', 'Goodbye')
 *        ->lower();
 *    // Arr helper
 *    $result = Arr::add(['name' => 'Desk'], 'price', 100)
 *        ->merge(['price' => 200])
 *        ->except(['price'])
 *        ->all();
 * 7. Symfony's OptionsResolver
 *    // Symfony OptionsResolver
 *    $resolver = new OptionsResolver();
 *    $resolver->setRequired('host')
 *        ->setAllowedTypes('host', 'string')
 *        ->setDefault('port', 80)
 *        ->setAllowedTypes('port', 'integer')
 *        ->setNormalizer('host', function (Options $options, $value) {
 *            return rtrim($value, '/');
 * });
 * 8. Zend Framework's Config
 *    // Zend Config
 *    $config = new Config([
 *        'production' => [
 *            'php_settings' => [
 *                'display_errors' => 0,
 *            ],
 *        ],
 *    ]);
 *    // Fluent modification
 *    $config->merge([
 *        'development' => [
 *            'php_settings' => [
 *                'display_errors' => 1,
 *            ],
 *        ],
 *    ]);
 * 9. Yii Framework's Html
 *    // Yii Html helper
 *    echo Html::a('Link', ['site/index'], [
 *        'class' => 'btn btn-primary',
 *        'data-method' => 'post',
 *    ]);
 * 10. CakePHP's Html
 *    // CakePHP Html helper
 *    echo $this->Html->link(
 *        'Link',
 *        ['controller' => 'pages', 'action' => 'home'],
 *        ['class' => 'btn', 'target' => '_blank']
 *    );
 * 11. Nette's Utils\Strings
 *    // Nette Strings
 *    $text = Strings::lower('Hello World')
 *        ->trim()
 *        ->replace('hello', 'hi')
 *        ->substring(0, 5);
 * 12. Doctrine's QueryBuilder
 *    // Doctrine QueryBuilder
 *    $query = $entityManager->createQueryBuilder()
 *        ->select('u')
 *        ->from('User', 'u')
 *        ->where('u.email = ?1')
 *        ->setParameter(1, 'john@example.com')
 *        ->getQuery();
 *
 *    $user = $query->getSingleResult();
 * 13. Laravel's Socialite
 *    // Socialite с Fluent Interface
 *    return Socialite::driver('github')
 *        ->with(['token' => '123'])
 *        ->redirect();
 * 14. Laravel's Storage
 *    // Storage facade
 *    Storage::disk('local')
 *        ->put('file.txt', 'Contents');
 * 15. Laravel's Cache
 *    // Cache facade
 *    Cache::remember('users', 60, function () {
 *        return User::all();
 *    });
 *
 * Когда полезен:
 *    Построение сложных объектов — SQL запросы, конфигурации, email
 * API клиенты — HTTP запросы, REST клиенты
 * Валидация — сложные правила валидации
 * Тестирование — создание тестовых данных
 * Fluent API — когда нужно предоставить выразительный API для клиентов
 */

try {
    echo "=== Fluent Interface Example ===\n\n";

    // SQL Query Builder
    $queryBuilder = new SqlQueryBuilder();

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


    echo "=== Fluent Email Builder Example ===\n\n";
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
    echo 'Attachments: ' . count($email['attachments']) . "\n\n";


    echo "=== Fluent HTTP Request Builder Example ===\n\n";
    $requestBuilder = new HttpRequestBuilder();

    $request = $requestBuilder
        ->method('POST')
        ->url('https://jsonplaceholder.typicode.com/posts')
        ->header('Content-Type', 'application/json')
        ->header('Accept', 'application/json')
        ->query(['page' => 1, 'limit' => 10])
        ->body([
            'title' => 'Fluent Interface',
            'body' => 'This is an example of fluent interface pattern',
            'userId' => 1,
        ])
        ->timeout(10)
        ->build();

    echo "HTTP Request:\n";
    echo "Method: {$request['method']}\n";
    echo "URL: {$request['url']}\n";
    echo 'Headers: ' . json_encode($request['headers']) . "\n";
    echo 'Body: ' . json_encode($request['body']) . "\n\n";

    // Send request
    $response = $requestBuilder->send();
    echo "HTTP Response Status: {$response['status']}\n";
    echo 'Response Body: ' . json_encode(json_decode($response['body'], true)) . "\n\n";


    echo "=== Fluent Configuration Builder Example ===\n\n";
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


    echo "=== Fluent Validation Builder Example ===\n\n";
    $validator = new ValidationBuilder();

    $validator->data([
        'email' => 'john@example.com',
        'age' => 25,
        'password' => 'secret123',
    ]);

    $validator
        ->required('email')
        ->email('email')
        ->required('age')
        ->numeric('age')
        ->between('age', 18, 100)
        ->required('password')
        ->min('password', 8);

    $result = $validator->validate();

    echo "Validation Result:\n";
    echo 'Success: ' . ($result->success ? 'YES' : 'NO') . "\n";
    if (!$result->success) {
        echo "Errors:\n";
        foreach ($result->errors as $field => $error) {
            echo " - $field: $error\n";
        }
    }

    echo "\nResetting validator...\n";
    $validator->reset();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}



/* php8.4  *******************************************

// Использование атрибутов для автоматического создания Fluent Builders
#[Fluent]
class UserQueryBuilder
{
    private string $name;
    private int $age;
    private string $email;

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function age(int $age): self
    {
        $this->age = $age;
        return $this;
    }

    public function email(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function build(): User
    {
        return new User($this->name, $this->age, $this->email);
    }
}

// Генерация Fluent Builders через атрибуты
class FluentBuilderGenerator
{
    public function generate(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstance();

        foreach ($reflector->getProperties() as $property) {
            $attributes = $property->getAttributes(FluentProperty::class);
            foreach ($attributes as $attribute) {
                // @var FluentProperty $fluent
                $fluent = $attribute->newInstance();
                $propertyName = $property->getName();
                $methodName = $fluent->name ?? $propertyName;

                // Создаем метод для fluent interface
                $method = function ($value) use ($instance, $propertyName) {
                    $instance->$propertyName = $value;
                    return $instance;
                };

                $reflector->getMethod($methodName)->setAccessible(true);
                $reflector->getMethod($methodName)->invoke($instance, $method);
            }
        }

        return $instance;
    }
}

// Pattern matching для автоматического создания Fluent Builders
public function getBuilder(string $type): object
{
    return match ($type) {
        'sql' => (new SqlQueryBuilder())
            ->select(['*'])
            ->from('users')
            ->where('active', 1),
        'email' => (new EmailBuilder())
            ->from('noreply@example.com')
            ->to('user@example.com')
            ->subject('Hello'),
        'http' => (new HttpRequestBuilder())
            ->method('GET')
            ->url('https://api.example.com'),
        'config' => (new ConfigBuilder())
            ->set('app.name', 'MyApp')
            ->set('debug', true),
        'validator' => (new ValidationBuilder())
            ->required('email')
            ->email('email'),
        default => throw new InvalidArgumentException("Unknown builder type: $type"),
    };
}

// Enum для типов Fluent Builders
enum BuilderType: string
{
    case SQL = 'sql';
    case EMAIL = 'email';
    case HTTP = 'http';
    case CONFIG = 'config';
    case VALIDATOR = 'validator';
    case USER = 'user';
    case PRODUCT = 'product';
    case ORDER = 'order';
}

class BuilderFactory
{
    public function create(BuilderType $type, array $dependencies = []): object
    {
        return match ($type) {
            BuilderType::SQL => (new SqlQueryBuilder())
                ->select(['*'])
                ->from('users'),
            BuilderType::EMAIL => (new EmailBuilder())
                ->from('noreply@example.com'),
            BuilderType::HTTP => (new HttpRequestBuilder())
                ->method('GET'),
            BuilderType::CONFIG => (new ConfigBuilder())
                ->set('app.name', 'MyApp'),
            BuilderType::VALIDATOR => (new ValidationBuilder())
                ->required('email'),
            BuilderType::USER => (new UserBuilder())
                ->name('John')
                ->email('john@example.com'),
            BuilderType::PRODUCT => (new ProductBuilder())
                ->name('Laptop')
                ->price(999.99),
            BuilderType::ORDER => (new OrderBuilder())
                ->userId(1)
                ->addItem(1, 2),
        };
    }
}

***************************************************** */

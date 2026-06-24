<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 24.06.2026 - 13:25
 */

namespace Behavioral\ChainOfResponsibilities;

/**
 * Handler Interface - интерфейс обработчика
 */
interface PaymentHandler
{
    public function setNext(PaymentHandler $handler): void;
    public function handle(PaymentRequest $request): PaymentResponse;
}

/**
 * Abstract Handler - абстрактный обработчик
 */
abstract class AbstractPaymentHandler implements PaymentHandler
{
    private ?PaymentHandler $next = null;

    public function setNext(PaymentHandler $handler): void
    {
        $this->next = $handler;
    }

    public function handle(PaymentRequest $request): PaymentResponse
    {
        if ($this->next !== null) {
            return $this->next->handle($request);
        }

        return new PaymentResponse(false, 'No handler available');
    }
}

/**
 * Concrete Handlers
 */
class CreditCardHandler extends AbstractPaymentHandler
{
    public function handle(PaymentRequest $request): PaymentResponse
    {
        if ($request->getMethod() === 'credit_card' && $request->getAmount() <= 1000) {
            return new PaymentResponse(true, 'Credit card payment processed');
        }

        return parent::handle($request);
    }
}

class PayPalHandler extends AbstractPaymentHandler
{
    public function handle(PaymentRequest $request): PaymentResponse
    {
        if ($request->getMethod() === 'paypal' && $request->getAmount() <= 5000) {
            return new PaymentResponse(true, 'PayPal payment processed');
        }

        return parent::handle($request);
    }
}

class BankTransferHandler extends AbstractPaymentHandler
{
    public function handle(PaymentRequest $request): PaymentResponse
    {
        if ($request->getMethod() === 'bank_transfer') {
            return new PaymentResponse(true, 'Bank transfer processed');
        }

        return parent::handle($request);
    }
}

class DefaultHandler extends AbstractPaymentHandler
{
    public function handle(PaymentRequest $request): PaymentResponse
    {
        return new PaymentResponse(false, 'Unsupported payment method');
    }
}

/**
 * Request and Response
 */
class PaymentRequest
{
    public function __construct(
        private string $method,
        private float $amount,
        private string $currency = 'USD'
    ) {}

public function getMethod(): string { return $this->method; }
public function getAmount(): float { return $this->amount; }
public function getCurrency(): string { return $this->currency; }
}

class PaymentResponse
{
    public function __construct(
        private bool $success,
        private string $message
    ) {}

public function isSuccess(): bool { return $this->success; }
public function getMessage(): string { return $this->message; }
}

/**
 * Пример 2: Middleware для HTTP запросов
 */
interface Middleware
{
    public function setNext(Middleware $next): void;
    public function handle(ServerRequest $request): Response;
}

abstract class AbstractMiddleware implements Middleware
{
    private ?Middleware $next = null;

    public function setNext(Middleware $next): void
    {
        $this->next = $next;
    }

    public function handle(ServerRequest $request): Response
    {
        if ($this->next !== null) {
            return $this->next->handle($request);
        }

        return new Response(200, 'OK');
    }
}

class AuthenticationMiddleware extends AbstractMiddleware
{
    public function handle(ServerRequest $request): Response
    {
        if (!$request->hasHeader('Authorization')) {
            return new Response(401, 'Unauthorized');
        }

        return parent::handle($request);
    }
}

class RateLimitMiddleware extends AbstractMiddleware
{
    private array $requestCounts = [];
    private int $limit = 100;
    private int $window = 60; // seconds

    public function handle(ServerRequest $request): Response
    {
        $ip = $request->getClientIp();
        $now = time();

        // Очистка старых запросов
        foreach ($this->requestCounts as $ipKey => $data) {
            if ($data['timestamp'] < $now - $this->window) {
                unset($this->requestCounts[$ipKey]);
            }
        }

        if (!isset($this->requestCounts[$ip])) {
            $this->requestCounts[$ip] = ['count' => 1, 'timestamp' => $now];
        } else {
            $this->requestCounts[$ip]['count']++;
        }

        if ($this->requestCounts[$ip]['count'] > $this->limit) {
            return new Response(429, 'Too Many Requests');
        }

        return parent::handle($request);
    }
}

class RequestLoggerMiddleware extends AbstractMiddleware
{
    public function handle(ServerRequest $request): Response
    {
        $log = sprintf(
            "[%s] %s %s - %s\n",
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getUri(),
            $request->getClientIp()
        );
        error_log($log);

        return parent::handle($request);
    }
}

/**
 * Пример 3: Валидация формы
 */
interface FormValidator
{
    public function setNext(FormValidator $next): void;
    public function validate(array $data): array;
}

abstract class AbstractFormValidator implements FormValidator
{
    private ?FormValidator $next = null;

    public function setNext(FormValidator $next): void
    {
        $this->next = $next;
    }

    public function validate(array $data): array
    {
        $errors = $this->check($data);

        if ($this->next !== null && empty($errors)) {
            return $this->next->validate($data);
        }

        return $errors;
    }

    abstract protected function check(array $data): array;
}

class RequiredFieldsValidator extends AbstractFormValidator
{
    private array $requiredFields;

    public function __construct(array $requiredFields)
    {
        $this->requiredFields = $requiredFields;
    }

    protected function check(array $data): array
    {
        $errors = [];
        foreach ($this->requiredFields as $field) {
            if (empty($data[$field] ?? '')) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        return $errors;
    }
}

class EmailValidator extends AbstractFormValidator
{
    private array $emailFields;

    public function __construct(array $emailFields)
    {
        $this->emailFields = $emailFields;
    }

    protected function check(array $data): array
    {
        $errors = [];
        foreach ($this->emailFields as $field) {
            if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Field '{$field}' must be a valid email";
            }
        }
        return $errors;
    }
}

class PasswordValidator extends AbstractFormValidator
{
    private array $passwordFields;
    private int $minLength = 8;

    public function __construct(array $passwordFields, int $minLength = 8)
    {
        $this->passwordFields = $passwordFields;
        $this->minLength = $minLength;
    }

    protected function check(array $data): array
    {
        $errors = [];
        foreach ($this->passwordFields as $field) {
            if (!empty($data[$field]) && strlen($data[$field]) < $this->minLength) {
                $errors[] = "Field '{$field}' must be at least {$this->minLength} characters";
            }
        }
        return $errors;
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Handler Interface - определяет методы setNext() и handle()
 *    Abstract Handler - реализует setNext() и базовую логику передачи запроса
 *    Concrete Handlers - реализуют handle() с собственной логиккой
 *    Client - создает цепочку и отправляет запрос первому обработчику
 *
 * Преимущества:
 *    Гибкость - легко добавлять/удалять обработчики
 *    Слабая связь - отправитель не знает, кто обработает запрос
 *    Динамичность - цепочка может изменяться во время выполнения
 *    Расширяемость - легко добавлять новые типы обработчиков
 * Недостатки:
 *    Производительность - запрос может пройти по всей цепочке
 *    Сложность отладки - трудно отследить, какой обработчик сработал
 *    Утечки - если ни один обработчик не обработал запрос
 *    Дублирование - обработчики могут выполнять одинаковую работу
 *
 * Где используется в фреймворках:
1. Laravel's Middleware
 *    class CheckAge
 *    {
 *        public function handle($request, Closure $next)
 *        {
 *            if ($request->user()->age <= 200) {
 *                return redirect('home');
 *            }
 *            return $next($request);
 *        }
 *    }
 *    // Цепочка middleware
 *    Route::middleware([CheckAge::class, LogVisits::class])->group(function () {
 *        // Routes
 *    });
 * 2. Symfony's Event Dispatcher
 *    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
 *    class OrderSubscriber implements EventSubscriberInterface
 *    {
 *        public static function getSubscribedEvents(): array
 *        {
 *            return [
 *                'order.created' => 'onOrderCreated',
 *                'order.paid' => 'onOrderPaid',
 *            ];
 *        }
 *        public function onOrderCreated(OrderCreated $event): void
 *        {
 *            // Обработка создания заказа
 *        }
 *    }
 *    // Диспетчер событий вызывает подписчиков по цепочке
 *    $dispatcher->dispatch('order.created', $event);
 * 3. Laravel's Pipeline
 *    use Illuminate\Pipeline\Pipeline;
 *    $pipeline = app(Pipeline::class)
 *        ->send($request)
 *        ->through([
 *            Authenticate::class,
 *            VerifyCsrfToken::class,
 *            ValidatePostSize::class,
 *        ])
 *        ->then(function ($request) {
 *            // Обработка завершена
 *        });
 * 4. Laravel's Query Filters
 *    class UserFilter
 *    {
 *        public function handle($query, Closure $next, $value)
 *        {
 *            if ($value === 'active') {
 *                $query->where('active', 1);
 *            }
 *            return $next($query);
 *        }
 *    }
 *    // Цепочка фильтров
 *    User::filter([new UserFilter('active')]);
 * 5. Laravel's Form Requests
 *    class StorePostRequest extends FormRequest
 *    {
 *        public function authorize()
 *        {
 *            return true;
 *        }
 *        public function rules()
 *        {
 *            return [
 *                'title' => 'required|max:255',
 *                'body' => 'required',
 *            ];
 *        }
 *        public function withValidator($validator)
 *        {
 *            $validator->after(function ($validator) {
 *                if ($this->somethingElseIsInvalid()) {
 *                    $validator->errors()->add('field', 'Something is wrong with this field!');
 *                }
 *            });
 *        }
 *    }
 * 6. Laravel's Exception Handler
 *    class Handler extends ExceptionHandler
 *    {
 *        protected $dontReport = [
 *            //
 *        ];
 *        protected $dontFlash = [
 *            //
 *        ];
 *        public function register()
 *        {
 *            $this->report(function (Throwable $e) {
 *                // Логирование
 *            })->render(function (Throwable $e) {
 *                // Отображение ошибки
 *            });
 *        }
 *    }
 *
 * Когда полезен:
 *    Middleware - обработка HTTP запросов
 *    Валидация - последовательная проверка данных
 *    Логирование - разные уровни логирования
 *    Обработка ошибок - цепочка обработчиков исключений
 *    Плагины - расширение функциональности
 *    Команды - обработка команд по цепочке
 */

echo "=== Payment Chain Example ===\n";

// Создаем цепочку обработчиков
$creditCardHandler = new CreditCardHandler();
$payPalHandler = new PayPalHandler();
$bankTransferHandler = new BankTransferHandler();
$defaultHandler = new DefaultHandler();

$creditCardHandler->setNext($payPalHandler);
$payPalHandler->setNext($bankTransferHandler);
$bankTransferHandler->setNext($defaultHandler);

// Обработка платежей
$requests = [
    new PaymentRequest('credit_card', 500),
    new PaymentRequest('paypal', 2000),
    new PaymentRequest('bank_transfer', 10000),
    new PaymentRequest('bitcoin', 100),
];

foreach ($requests as $i => $request) {
    $response = $creditCardHandler->handle($request);
    echo "Payment #{$i+1}: " . ($response->isSuccess() ? 'SUCCESS' : 'FAILED')
        . " - " . $response->getMessage() . "\n";
}

echo "\n=== Middleware Chain Example ===\n";

// Создаем цепочку middleware
$authMiddleware = new AuthenticationMiddleware();
$rateLimitMiddleware = new RateLimitMiddleware();
$loggerMiddleware = new RequestLoggerMiddleware();

$authMiddleware->setNext($rateLimitMiddleware);
$rateLimitMiddleware->setNext($loggerMiddleware);

// Симуляция запросов
$requests = [
    new ServerRequest('GET', '/api/users', ['Authorization' => 'Bearer token'], '192.168.1.1'),
    new ServerRequest('POST', '/api/orders', [], '192.168.1.2'),
    new ServerRequest('GET', '/api/products', ['Authorization' => 'Bearer token'], '192.168.1.1'),
];

foreach ($requests as $i => $request) {
    $response = $authMiddleware->handle($request);
    echo "Request #{$i+1}: " . $response->getStatus() . " " . $response->getBody() . "\n";
}

echo "\n=== Form Validation Chain Example ===\n";

// Создаем цепочку валидаторов
$requiredValidator = new RequiredFieldsValidator(['username', 'email', 'password']);
$emailValidator = new EmailValidator(['email']);
$passwordValidator = new PasswordValidator(['password'], 8);

$requiredValidator->setNext($emailValidator);
$emailValidator->setNext($passwordValidator);

// Валидация данных
$forms = [
    [
        'username' => 'john_doe',
        'email' => 'john@example.com',
        'password' => 'secrEt123'
    ],
    [
        'username' => '',
        'email' => 'invalid-email',
        'password' => 'short'
    ],
    [
        'username' => 'jane_doe',
        'email' => 'jane@example.com',
        'password' => 'valIDpass123'
    ],
];

foreach ($forms as $i => $form) {
    $errors = $requiredValidator->validate($form);
    if (empty($errors)) {
        echo "Form #{$i+1}: VALID\n";
    } else {
        echo "Form #{$i+1}: INVALID\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }
}


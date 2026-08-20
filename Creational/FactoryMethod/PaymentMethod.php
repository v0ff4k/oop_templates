<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 06.08.2026 - 15:18
 * upd: 20.08.2026 - 21:49  make real FactoryMethod +DependencyInversion
 */

namespace Creational\FactoryMethod;

use \PDO;

// dummy stubs(in real examples, use real classes)
// use  \Twig\Environment as TwigEnvironment
use stdClass as TwigEnvironment;
// user Twig\Loader\ArrayLoader as TwigLoaderArrayLoader
use stdClass as TwigLoaderArrayLoader;
// use \Illuminate\View\Factory as IlluminateViewFactory
use stdClass as IlluminateViewFactory;
// use \Illuminate\View\FileViewFinder as IlluminateViewFileViewFinder
use stdClass as IlluminateViewFileViewFinder;

/**
 * Product - интерфейс для создаваемых объектов
 *  Пример 1: Payment Factory Method
 */
interface PaymentMethod
{
    public function process(float $amount): string;

    public function getFee(): float;
}

/**
 * Пример 2: Factory Method для логгеров
 */
interface Logger
{
    public function log(string $message, array $context = []): void;
}

/**
 * Пример 3: Factory Method для отправки уведомлений
 */
interface Notifier
{
    public function send(string $message): bool;
}

/**
 * Пример 4: Factory Method для валидаторов
 */
interface Validator
{
    public function validate(mixed $data): bool;

    public function getErrors(): array;
}

/**
 * Пример 5: Factory Method для рендереров
 */
interface Renderer
{
    public function render(string $template, array $data): string;
}

/**
 * Concrete Product 1 - Кредитная карта
 */
class CreditCardPayment implements PaymentMethod
{
    public function process(float $amount): string
    {
        return "Credit card payment of \${$amount} processed";
    }

    public function getFee(): float
    {
        return $amount * 0.025; // 2.5% fee
    }
}

/**
 * Concrete Product 2 - PayPal
 */
class PayPalPayment implements PaymentMethod
{
    public function process(float $amount): string
    {
        return "PayPal payment of \${$amount} processed";
    }

    public function getFee(): float
    {
        return $amount * 0.03; // 3% fee
    }
}

/**
 * Concrete Product 3 - Банковский перевод
 */
class BankTransferPayment implements PaymentMethod
{
    public function process(float $amount): string
    {
        return "Bank transfer of \${$amount} processed";
    }

    public function getFee(): float
    {
        return 0; // No fee
    }
}

/**
 * Creator - класс, который использует Factory Method
 */
abstract class PaymentCreator
{
    /**
     * Основная бизнес-логика, которая использует созданный объект
     */
    public function processPayment(float $amount): string
    {
        $payment = $this->createPayment();
        $fee = $payment->getFee();
        $total = $amount + $fee;

        return $payment->process($total)." (Fee: \${$fee})";
    }

    /**
     * Factory Method - создает объект, но не указывает конкретный класс
     */
    abstract protected function createPayment(): PaymentMethod;
}

/**
 * Concrete Creator 1 - Создатель для кредитных карт
 */
class CreditCardCreator extends PaymentCreator
{
    protected function createPayment(): PaymentMethod
    {
        return new CreditCardPayment();
    }
}

/**
 * Concrete Creator 2 - Создатель для PayPal
 */
class PayPalCreator extends PaymentCreator
{
    protected function createPayment(): PaymentMethod
    {
        return new PayPalPayment();
    }
}

/**
 * Concrete Creator 3 - Создатель для банковских переводов
 */
class BankTransferCreator extends PaymentCreator
{
    protected function createPayment(): PaymentMethod
    {
        return new BankTransferPayment();
    }
}

class FileLogger implements Logger
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function log(string $message, array $context = []): void
    {
        $logEntry = date('Y-m-d H:i:s')." - $message\n";
        file_put_contents($this->filePath, $logEntry, FILE_APPEND);
    }
}

class DatabaseLogger implements Logger
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function log(string $message, array $context = []): void
    {
        $stmt = $this->connection->prepare('INSERT INTO logs (message, context) VALUES (?, ?)');
        $stmt->execute([$message, json_encode($context)]);
    }
}

class SyslogLogger implements Logger
{
    public function log(string $message, array $context = []): void
    {
        syslog(LOG_INFO, $message);
    }
}

abstract class LoggerFactory
{
    private ?Logger $lastLogger = null;

    public function log(string $message, array $context = []): void
    {
        $logger = $this->createLogger();
        $this->lastLogger = $logger;
        $logger->log($message, $context);
    }

    abstract protected function createLogger(): Logger;

    public function getErrors(): array
    {
        if (!$this->lastLogger instanceof Logger) {
            return [];
        }
        // Для простоты возвращаем пустой массив, так как логи не имеют ошибок валидации
        return [];
    }
}

class FileLoggerFactory extends LoggerFactory
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    protected function createLogger(): Logger
    {
        return new FileLogger($this->filePath);
    }
}

class DatabaseLoggerFactory extends LoggerFactory
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    protected function createLogger(): Logger
    {
        return new DatabaseLogger($this->connection);
    }
}

class SyslogLoggerFactory extends LoggerFactory
{
    protected function createLogger(): Logger
    {
        return new SyslogLogger();
    }
}

class EmailNotifier implements Notifier
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    public function send(string $message): bool
    {
        // Здесь реальная отправка email
        echo "Email sent from {$this->from}: $message\n";

        return true;
    }
}

class SmsNotifier implements Notifier
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    public function send(string $message): bool
    {
        // Здесь реальная отправка SMS
        echo "SMS sent from {$this->from}: $message\n";

        return true;
    }
}

class PushNotifier implements Notifier
{
    private string $endpoint;

    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function send(string $message): bool
    {
        // Здесь реальная отправка push-уведомления
        echo "Push notification sent to {$this->endpoint}: $message\n";

        return true;
    }
}

abstract class NotifierFactory
{
    private ?Notifier $lastNotifier = null;

    public function notify(string $message): bool
    {
        $notifier = $this->createNotifier();
        $this->lastNotifier = $notifier;

        return $notifier->send($message);
    }

    abstract protected function createNotifier(): Notifier;

    public function getErrors(): array
    {
        if (!$this->lastNotifier instanceof Notifier) {
            return [];
        }
        // Для простоты возвращаем пустой массив, так как уведомления не имеют ошибок валидации
        return [];
    }
}

class EmailNotifierFactory extends NotifierFactory
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    protected function createNotifier(): Notifier
    {
        return new EmailNotifier($this->from);
    }
}

class SmsNotifierFactory extends NotifierFactory
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    protected function createNotifier(): Notifier
    {
        return new SmsNotifier($this->from);
    }
}

class PushNotifierFactory extends NotifierFactory
{
    private string $endpoint;

    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    protected function createNotifier(): Notifier
    {
        return new PushNotifier($this->endpoint);
    }
}

class EmailValidator implements Validator
{
    private array $errors = [];

    public function validate(mixed $data): bool
    {
        if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';

            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class RequiredValidator implements Validator
{
    private array $errors = [];

    public function validate(mixed $data): bool
    {
        if (empty($data) && $data !== 0 && $data !== false) {
            $this->errors[] = 'This field is required';

            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class MinLengthValidator implements Validator
{
    private int $minLength;
    private array $errors = [];

    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    public function validate(mixed $data): bool
    {
        if (is_string($data) && strlen($data) < $this->minLength) {
            $this->errors[] = "Minimum length is {$this->minLength} characters";

            return false;
        }

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

abstract class ValidatorFactory
{
    private ?Validator $lastValidator = null;

    public function validate(mixed $data): bool
    {
        $validator = $this->createValidator();
        $this->lastValidator = $validator;

        return $validator->validate($data);
    }

    abstract protected function createValidator(): Validator;

    // возвращает ошибки из последней валидации
    public function getErrors(): array
    {
        if (!$this->lastValidator instanceof Validator) {
            // Левый валидатор, пустой массив

            return [];
        }

        return $this->lastValidator->getErrors();
    }
}

class EmailValidatorFactory extends ValidatorFactory
{
    protected function createValidator(): Validator
    {
        return new EmailValidator();
    }
}

class RequiredValidatorFactory extends ValidatorFactory
{
    protected function createValidator(): Validator
    {
        return new RequiredValidator();
    }
}

class MinLengthValidatorFactory extends ValidatorFactory
{
    private int $minLength;

    public function __construct(int $minLength)
    {
        $this->minLength = $minLength;
    }

    protected function createValidator(): Validator
    {
        return new MinLengthValidator($this->minLength);
    }
}

class PhpRenderer implements Renderer
{
    public function render(string $template, array $data): string
    {
        extract($data);
        ob_start();
        include $template;

        return ob_get_clean();
    }
}

class TwigRenderer implements Renderer
{
    /** @var object */
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        /** @var object{method: render(string, array): object} $twig */
        $this->twig = $twig;
    }

    public function render(string $template, array $data): string
    {
        return $this->twig->render($template, $data);
    }
}

class BladeRenderer implements Renderer
{
    /** @var object */
    private IlluminateViewFactory $blade;

    public function __construct(IlluminateViewFactory $blade)
    {
        /** @var object{method: make(string, array): object} $blade */
        $this->blade = $blade;
    }

    public function render(string $template, array $data): string
    {
        return $this->blade->make($template, $data)->render();
    }
}

abstract class RendererFactory
{
    public function render(string $template, array $data): string
    {
        $renderer = $this->createRenderer();

        return $renderer->render($template, $data);
    }

    abstract protected function createRenderer(): Renderer;
}

class PhpRendererFactory extends RendererFactory
{
    protected function createRenderer(): Renderer
    {
        return new PhpRenderer();
    }
}

class TwigRendererFactory extends RendererFactory
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    protected function createRenderer(): Renderer
    {
        return new TwigRenderer($this->twig);
    }
}

class BladeRendererFactory extends RendererFactory
{
    private IlluminateViewFactory $blade;

    public function __construct(IlluminateViewFactory $blade)
    {
        $this->blade = $blade;
    }

    protected function createRenderer(): Renderer
    {
        return new BladeRenderer($this->blade);
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Product — интерфейс для создаваемых объектов
 *    ConcreteProduct — конкретная реализация Product
 *    Creator — класс, который использует Factory Method для создания объектов
 *    ConcreteCreator — подкласс Creator, который переопределяет Factory Method для создания конкретных продуктов
 *
 * Как это работает:
 *    Product — интерфейс для создаваемых объектов
 *    ConcreteProduct — конкретная реализация Product
 *    Creator — класс, который объявляет Factory Method
 *    ConcreteCreator — подкласс Creator, который реализует Factory Method
 *    Клиент — использует Creator и ConcreteCreator для создания объектов
 *
 * Преимущества:
 *    Гибкость — легко добавлять новые типы продуктов
 *    Расширяемость — можно создавать новые ConcreteCreator без изменения базового класса
 *    Инверсия зависимостей — клиент зависит от абстракций, а не конкретных классов
 *    Поддержка принципов SOLID — особенно Open/Closed Principle и Dependency Inversion
 *    Тестируемость — легко мокировать Factory Method в тестах
 * Недостатки:
 *    Сложность — добавляет дополнительный уровень абстракции
 *    Много классов — для каждого продукта нужен свой Creator
 *    Избыточность — иногда проще использовать простой конструктор
 *    Сложность отладки — может быть сложнее отлаживать из-за иерархии наследования
 *
 *
 * Где используется в фреймворках:
 * Laravel's Mailable
 *    // Laravel Mailable - Factory Method для email
 *    class OrderShipped extends Mailable {
 *        use Queueable, SerializesModels;
 *        public $order;
 *        public function __construct(Order $order) {
 *            $this->order = $order;
 *        }
 *        public function build() {
 *            return $this
 *                ->view('emails.orders.shipped')
 *                ->with(['order' => $this->order])
 *                ->subject('Your order has been shipped!');
 *        }
 *    }
 *    // Использование
 *    Mail::to($user)->send(new OrderShipped($order));
 * Laravel's Notification
 *    // Laravel Notification - Factory Method для уведомлений
 *    class OrderShipped extends Notification {
 *        public function via($notifiable) {
 *            return ['mail', 'database'];
 *        }
 *        public function toMail($notifiable) {
 *            return (new MailMessage)
 *                ->line('Your order has been shipped!')
 *                ->action('View Order', url('/order/'.$this->order->id))
 *                ->line('Thank you for using our service!');
 *        }
 *        public function toArray($notifiable) {
 *            return [
 *                'order_id' => $this->order->id,
 *                'status' => 'shipped',
 *            ];
 *        }
 *    }
 *    // Использование
 *    $user->notify(new OrderShipped($order));
 * Laravel's Request Validation
 *    // Laravel Form Request - Factory Method для валидации
 *    class StorePostRequest extends FormRequest {
 *        public function authorize() {
 *            return true;
 *        }
 *        public function rules() {
 *            return [
 *                'title' => 'required|string|max:255',
 *                'body' => 'required|string',
 *            ];
 *        }
 *        public function messages() {
 *            return [
 *                'title.required' => 'A title is required',
 *            ];
 *        }
 *    }
 *    // Использование
 *    public function store(StorePostRequest $request) {
 *        $validated = $request->validated();
 *        // ...
 *    }
 * Symfony's Controller
 *    // Symfony(2+) Controller - Factory Method для действий
 *    // Это просто action-метод, а не фабричный метод
 *    // Наследование от AbstractController не создает продукты
 * Symfony's Form Type
 *    // Symfony Form Type - Factory Method для форм
 *    // Это конфигурация формы, а не фабричный метод
 *    // createForm() в Symfony — это фабричный метод, но UserType — это просто конфигурация
 * Yii Framework's Action
 *    // Yii Action - Factory Method для действий контроллера
 *    // Это массив конфигураций, а не фабричный метод
 *    // Нет наследования с переопределением метода создания
 * CakePHP's View Block
 *    // CakePHP View Block - Factory Method для блоков шаблонов
 *    // Простое переопределение метода, не фабрика
 * Nette's UI Component
 *    // Nette UI Component - Factory Method для компонентов
 *    // Конструктор создает компонент, но это не фабричный метод
 *    // Нет базового класса с абстрактным методом создания
 * Doctrine's Entity Listener
 *    // Doctrine Entity Listener - Factory Method для событий
 *    // Это событие, а не фабрика
 * Laravel's Service Container
 *    // Laravel Service Container - Factory Method для сервисов
 *    // Это Service Container (IoC контейнер), а не Factory Method
 *    //  $app->singleton() — это регистрация в контейнере, не фабрика
 * Laravel's Factory Method
 *    // Creator (абстрактный создатель)
 *    abstract class Mailable {
 *        abstract public function build(): Mailable;
 *    }
 *    // ConcreteCreator (конкретный создатель)
 *    class OrderShipped extends Mailable {
 *        public function build(): Mailable {
 *            return $this->view('emails.orders.shipped');
 *        }
 *    }
 *    // Product (интерфейс продукта)
 *    interface Mailable {}
 *    // Клиентский код:
 *    Mail::to($user)->send(new OrderShipped($order));
 * Symfony's Factory Method
 *    // Creator
 *    abstract class AbstractController {
 *        abstract protected function createResponse(): Response;
 *    }
 *    // ConcreteCreator
 *    class DefaultController extends AbstractController {
 *        protected function createResponse(): Response {
 *            return $this->render('default/index.html.twig');
 *        }
 *        public function index(): Response {
 *            return $this->createResponse();
 *        }
 *    }
 *
 * Ключевое отличие:
 *    Factory Method = Creator (абстрактный) -> ConcreteCreator (конкретный) -> Product
 *    Остальные примеры = либо конфигурация, либо Service Container, либо просто методы
 *
 *
 * Когда полезен:
 *    Когда нужно создать объект, но не знаешь конкретный класс — клиент работает с абстракциями
 *    Когда нужно расширять функциональность — новые продукты добавляются через наследование
 *    Когда нужно инкапсулировать создание объектов — логика создания скрыта в Creator
 *    Когда нужно следовать принципу Open/Closed — система открыта для расширения, но закрыта для модификации
 *
 *
 * Разница между Factory Method и другими паттернами:
 * Factory Method = Создает один продукт +Через наследование +Фокус на создании +Простой продукт
 * Abstract Factory = Создает семейство продуктов +Через композицию +Фокус на совместимости +Семейство продуктов
 * Builder = Создает сложный объект пошагово +Через директор и билдер +Фокус на процессе +Сложный продукт
 * Prototype = Клонирует существующий объект +Через клонирование +Фокус на клонировании +Любой объект
 *
 */
try {
    echo "=== Factory Method Pattern Example ===\n\n";

    // Пример 1: Payment Factory Method
    echo "=== Payment Factory Method ===\n";

    $amount = 100.00;

    $creditCardCreator = new CreditCardCreator();
    $paypalCreator = new PayPalCreator();
    $bankTransferCreator = new BankTransferCreator();

    echo 'Credit Card Payment: '.$creditCardCreator->processPayment($amount)."\n";
    echo 'PayPal Payment: '.$paypalCreator->processPayment($amount)."\n";
    echo 'Bank Transfer: '.$bankTransferCreator->processPayment($amount)."\n\n";


    // Пример 2: Logger Factory Method
    echo "=== Logger Factory Method ===\n";

    $fileLoggerFactory = new FileLoggerFactory('/tmp/app.log');
    $databaseLoggerFactory = new DatabaseLoggerFactory(new PDO('sqlite::memory:'));
    $syslogLoggerFactory = new SyslogLoggerFactory();

    $fileLoggerFactory->log('System started', ['user' => 'admin']);
    $databaseLoggerFactory->log('User logged in', ['user_id' => 123]);
    $syslogLoggerFactory->log('Configuration loaded', ['config' => 'v1.0']);

    echo "Logs written successfully\n\n";


    // Пример 3: Notifier Factory Method
    echo "=== Notifier Factory Method ===\n";

    $emailNotifierFactory = new EmailNotifierFactory('noreply@example.com');
    $smsNotifierFactory = new SmsNotifierFactory('+1234567890');
    $pushNotifierFactory = new PushNotifierFactory('device_token_123');

    $emailNotifierFactory->notify('Welcome to our service!');
    $smsNotifierFactory->notify('Your verification code is 123456');
    $pushNotifierFactory->notify('New message received');

    echo "Notifications sent\n\n";


    // Пример 4: Validator Factory Method
    echo "=== Validator Factory Method ===\n";

    $emailValidatorFactory = new EmailValidatorFactory();
    $requiredValidatorFactory = new RequiredValidatorFactory();
    $minLengthValidatorFactory = new MinLengthValidatorFactory(6);

    $email = 'test@example.com';
    $password = '123';
    $username = '';

    echo 'Email validation: '.($emailValidatorFactory->validate($email) ? 'Valid' : 'Invalid')."\n";
    echo 'Password validation: '.($minLengthValidatorFactory->validate($password) ? 'Valid' : 'Invalid')."\n";
    echo 'Username validation: '.($requiredValidatorFactory->validate($username) ? 'Valid' : 'Invalid')."\n";

    echo "Validation completed\n\n";


    // Пример 5: Renderer Factory Method
    echo "=== Renderer Factory Method ===\n";

    $phpRendererFactory = new PhpRendererFactory();
    $twigRendererFactory = new TwigRendererFactory(
        new TwigEnvironment(new TwigLoaderArrayLoader([]))
    );
    $bladeRendererFactory = new BladeRendererFactory(
        new IlluminateViewFactory(new IlluminateViewFileViewFinder([]))
    );

    $template = 'Hello, {{ name }}!';
    $data = ['name' => 'John'];

    echo 'PHP Renderer: '.$phpRendererFactory->render($template, $data)."\n";
    echo 'Twig Renderer: '.$twigRendererFactory->render($template, $data)."\n";
    echo 'Blade Renderer: '.$bladeRendererFactory->render($template, $data)."\n";

} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}


/* 8.4 ****************

// 1. Product
interface Logger {
    public function log(string $message): void;
}

// 2. Concrete Products
class FileLogger implements Logger {
    public function log(string $message): void {
        echo "File: $message\n";
    }
}

class DatabaseLogger implements Logger {
    public function log(string $message): void {
        echo "DB: $message\n";
    }
}

// 3. Creator с атрибутом
abstract class LoggerFactory {
    #[FactoryMethod]
    abstract protected function createLogger(): Logger;

    public function log(string $message): void {
        $logger = $this->createLogger();
        $logger->log($message);
    }
}

// 4. Concrete Creators
class FileLoggerFactory extends LoggerFactory {
    #[FactoryMethod]
    protected function createLogger(): Logger {
        return new FileLogger();
    }
}

class DatabaseLoggerFactory extends LoggerFactory {
    #[FactoryMethod]
    protected function createLogger(): Logger {
        return new DatabaseLogger();
    }
}

// 5. Клиент
$factory = new FileLoggerFactory();
$factory->log("System started");  // Output: File: System started

****************************************************** */

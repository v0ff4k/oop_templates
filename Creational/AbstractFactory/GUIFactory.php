<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 03.08.2026 - 22:40
 */

namespace Creational\AbstractFactory;

/**
 * Abstract Factory - интерфейс для создания семейств объектов
 * Пример 1: GUI Factory
 */
interface GUIFactory
{
    public function createButton(): Button;
    public function createCheckbox(): Checkbox;
    public function createTextField(): TextField;
}

/**
 * Concrete Factory 1 - Windows стиль
 */
class WindowsFactory implements GUIFactory
{
    public function createButton(): Button
    {
        return new WindowsButton();
    }

    public function createCheckbox(): Checkbox
    {
        return new WindowsCheckbox();
    }

    public function createTextField(): TextField
    {
        return new WindowsTextField();
    }
}

/**
 * Concrete Factory 2 - macOS стиль
 */
class MacOSFactory implements GUIFactory
{
    public function createButton(): Button
    {
        return new MacOSButton();
    }

    public function createCheckbox(): Checkbox
    {
        return new MacOSCheckbox();
    }

    public function createTextField(): TextField
    {
        return new MacOSTextField();
    }
}

/**
 * Abstract Product - интерфейс для кнопок
 */
interface Button
{
    public function paint(): string;
    public function onClick(): string;
}

/**
 * Concrete Product 1 - Windows кнопка
 */
class WindowsButton implements Button
{
    public function paint(): string
    {
        return 'Windows Button (blue, square)';
    }

    public function onClick(): string
    {
        return 'Windows Button clicked!';
    }
}

/**
 * Concrete Product 2 - macOS кнопка
 */
class MacOSButton implements Button
{
    public function paint(): string
    {
        return 'macOS Button (gray, rounded)';
    }

    public function onClick(): string
    {
        return 'macOS Button clicked!';
    }
}

/**
 * Abstract Product - интерфейс для чекбоксов
 */
interface Checkbox
{
    public function paint(): string;
    public function toggle(): string;
}

/**
 * Concrete Product 1 - Windows чекбокс
 */
class WindowsCheckbox implements Checkbox
{
    public function paint(): string
    {
        return 'Windows Checkbox (square)';
    }

    public function toggle(): string
    {
        return 'Windows Checkbox toggled!';
    }
}

/**
 * Concrete Product 2 - macOS чекбокс
 */
class MacOSCheckbox implements Checkbox
{
    public function paint(): string
    {
        return 'macOS Checkbox (rounded)';
    }

    public function toggle(): string
    {
        return 'macOS Checkbox toggled!';
    }
}

/**
 * Abstract Product - интерфейс для текстовых полей
 */
interface TextField
{
    public function paint(): string;
    public function getText(): string;
}

/**
 * Concrete Product 1 - Windows текстовое поле
 */
class WindowsTextField implements TextField
{
    public function paint(): string
    {
        return 'Windows TextField (square, white)';
    }

    public function getText(): string
    {
        return 'Windows text input';
    }
}

/**
 * Concrete Product 2 - macOS текстовое поле
 */
class MacOSTextField implements TextField
{
    public function paint(): string
    {
        return 'macOS TextField (rounded, light gray)';
    }

    public function getText(): string
    {
        return 'macOS text input';
    }
}

/**
 * Пример 2: Abstract Factory для форматов файлов
 */
interface DocumentFactory
{
    public function createParagraph(string $text): Paragraph;
    public function createTable(array $rows): Table;
    public function createImage(string $path): Image;
}

class PdfDocumentFactory implements DocumentFactory
{
    public function createParagraph(string $text): Paragraph
    {
        return new PdfParagraph($text);
    }

    public function createTable(array $rows): Table
    {
        return new PdfTable($rows);
    }

    public function createImage(string $path): Image
    {
        return new PdfImage($path);
    }
}

class HtmlDocumentFactory implements DocumentFactory
{
    public function createParagraph(string $text): Paragraph
    {
        return new HtmlParagraph($text);
    }

    public function createTable(array $rows): Table
    {
        return new HtmlTable($rows);
    }

    public function createImage(string $path): Image
    {
        return new HtmlImage($path);
    }
}

interface Paragraph
{
    public function render(): string;
}

class PdfParagraph implements Paragraph
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function render(): string
    {
        return "<pdf:paragraph>{$this->text}</pdf:paragraph>";
    }
}

class HtmlParagraph implements Paragraph
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function render(): string
    {
        return "<p>{$this->text}</p>";
    }
}

interface Table
{
    public function render(): string;
}

class PdfTable implements Table
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function render(): string
    {
        $html = '<pdf:table>';
        foreach ($this->rows as $row) {
            $html .= '<pdf:row>';
            foreach ($row as $cell) {
                $html .= "<pdf:cell>$cell</pdf:cell>";
            }
            $html .= '</pdf:row>';
        }
        $html .= '</pdf:table>';
        return $html;
    }
}

class HtmlTable implements Table
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function render(): string
    {
        $html = '<table>';
        foreach ($this->rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= "<td>$cell</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
}

interface Image
{
    public function render(): string;
}

class PdfImage implements Image
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function render(): string
    {
        return "<pdf:image src='{$this->path}' />";
    }
}

class HtmlImage implements Image
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function render(): string
    {
        return "<img src='{$this->path}' alt='image' />";
    }
}

/**
 * Пример 3: Abstract Factory для платежных систем
 */
interface PaymentFactory
{
    public function createGateway(): PaymentGateway;
    public function createLogger(): PaymentLogger;
    public function createValidator(): PaymentValidator;
}

class StripePaymentFactory implements PaymentFactory
{
    public function createGateway(): PaymentGateway
    {
        return new StripeGateway();
    }

    public function createLogger(): PaymentLogger
    {
        return new StripeLogger();
    }

    public function createValidator(): PaymentValidator
    {
        return new StripeValidator();
    }
}

class PayPalPaymentFactory implements PaymentFactory
{
    public function createGateway(): PaymentGateway
    {
        return new PayPalGateway();
    }

    public function createLogger(): PaymentLogger
    {
        return new PayPalLogger();
    }

    public function createValidator(): PaymentValidator
    {
        return new PayPalValidator();
    }
}

interface PaymentGateway
{
    public function charge(float $amount, string $currency): PaymentResult;
}

interface PaymentLogger
{
    public function log(string $message): void;
}

interface PaymentValidator
{
    public function validate(array $data): bool;
}

class StripeGateway implements PaymentGateway
{
    public function charge(float $amount, string $currency): PaymentResult
    {
        return new PaymentResult('stripe_charge_123', $amount, $currency, true);
    }
}

class StripeLogger implements PaymentLogger
{
    public function log(string $message): void
    {
        echo "Stripe: $message\n";
    }
}

class StripeValidator implements PaymentValidator
{
    public function validate(array $data): bool
    {
        return isset($data['card_token']) && !empty($data['card_token']);
    }
}

class PayPalGateway implements PaymentGateway
{
    public function charge(float $amount, string $currency): PaymentResult
    {
        return new PaymentResult('paypal_payment_456', $amount, $currency, true);
    }
}

class PayPalLogger implements PaymentLogger
{
    public function log(string $message): void
    {
        echo "PayPal: $message\n";
    }
}

class PayPalValidator implements PaymentValidator
{
    public function validate(array $data): bool
    {
        return isset($data['paypal_email']) && filter_var($data['paypal_email'], FILTER_VALIDATE_EMAIL);
    }
}

class PaymentResult
{
    public function __construct(
        public string $transactionId,
        public float $amount,
        public string $currency,
        public bool $success
    ) {
    }
}

/**
 * Пример 4: Abstract Factory для валют
 */
interface CurrencyFactory
{
    public function createCurrency(string $code): Currency;
    public function createFormatter(string $locale): CurrencyFormatter;
    public function getExchangeRate(string $from, string $to): float;
}

class DefaultCurrencyFactory implements CurrencyFactory
{
    public function createCurrency(string $code): Currency
    {
        return match ($code) {
            'USD' => new USD(),
            'EUR' => new EUR(),
            'GBP' => new GBP(),
            'JPY' => new JPY(),
            default => throw new \InvalidArgumentException("Unknown currency: $code"),
        };
    }

    public function createFormatter(string $locale): CurrencyFormatter
    {
        return new DefaultCurrencyFormatter($locale);
    }

    public function getExchangeRate(string $from, string $to): float
    {
        $rates = [
            'USD' => ['EUR' => 0.85, 'GBP' => 0.75, 'JPY' => 110.0],
            'EUR' => ['USD' => 1.18, 'GBP' => 0.88, 'JPY' => 129.0],
            'GBP' => ['USD' => 1.33, 'EUR' => 1.14, 'JPY' => 147.0],
            'JPY' => ['USD' => 0.0091, 'EUR' => 0.0078, 'GBP' => 0.0068],
        ];

        return $rates[$from][$to] ?? 1.0;
    }
}

interface Currency
{
    public function getCode(): string;
    public function getSymbol(): string;
    public function getRate(): float;
}

class USD implements Currency
{
    public function getCode(): string
    {
        return 'USD';
    }
    public function getSymbol(): string
    {
        return '$';
    }
    public function getRate(): float
    {
        return 1.0;
    }
}

class EUR implements Currency
{
    public function getCode(): string
    {
        return 'EUR';
    }
    public function getSymbol(): string
    {
        return '€';
    }
    public function getRate(): float
    {
        return 0.85;
    }
}

class GBP implements Currency
{
    public function getCode(): string
    {
        return 'GBP';
    }
    public function getSymbol(): string
    {
        return '£';
    }
    public function getRate(): float
    {
        return 0.75;
    }
}

class JPY implements Currency
{
    public function getCode(): string
    {
        return 'JPY';
    }
    public function getSymbol(): string
    {
        return '¥';
    }
    public function getRate(): float
    {
        return 110.0;
    }
}

interface CurrencyFormatter
{
    public function format(float $amount, Currency $currency): string;
}

class DefaultCurrencyFormatter implements CurrencyFormatter
{
    private string $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function format(float $amount, Currency $currency): string
    {
        $formatted = number_format($amount, 2);
        return "{$currency->getSymbol()} $formatted";
    }
}

/**
 * Пример 5: Abstract Factory для тем UI
 */
interface ThemeFactory
{
    public function createButtonStyle(): ButtonStyle;
    public function createTextColor(): TextColor;
    public function createBackgroundColor(): BackgroundColor;
}

class LightThemeFactory implements ThemeFactory
{
    public function createButtonStyle(): ButtonStyle
    {
        return new LightButtonStyle();
    }

    public function createTextColor(): TextColor
    {
        return new LightTextColor();
    }

    public function createBackgroundColor(): BackgroundColor
    {
        return new LightBackgroundColor();
    }
}

class DarkThemeFactory implements ThemeFactory
{
    public function createButtonStyle(): ButtonStyle
    {
        return new DarkButtonStyle();
    }

    public function createTextColor(): TextColor
    {
        return new DarkTextColor();
    }

    public function createBackgroundColor(): BackgroundColor
    {
        return new DarkBackgroundColor();
    }
}

interface ButtonStyle
{
    public function getStyle(): array;
}

class LightButtonStyle implements ButtonStyle
{
    public function getStyle(): array
    {
        return [
            'background' => '#007bff',
            'color' => '#ffffff',
            'border' => 'none',
            'padding' => '10px 20px',
            'border-radius' => '4px',
        ];
    }
}

class DarkButtonStyle implements ButtonStyle
{
    public function getStyle(): array
    {
        return [
            'background' => '#6c757d',
            'color' => '#ffffff',
            'border' => 'none',
            'padding' => '10px 20px',
            'border-radius' => '4px',
        ];
    }
}

interface TextColor
{
    public function getColor(): string;
}

class LightTextColor implements TextColor
{
    public function getColor(): string
    {
        return '#333333';
    }
}

class DarkTextColor implements TextColor
{
    public function getColor(): string
    {
        return '#f8f9fa';
    }
}

interface BackgroundColor
{
    public function getColor(): string;
}

class LightBackgroundColor implements BackgroundColor
{
    public function getColor(): string
    {
        return '#ffffff';
    }
}

class DarkBackgroundColor implements BackgroundColor
{
    public function getColor(): string
    {
        return '#212529';
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    AbstractFactory — интерфейс для создания всех объектов семейства
 *    ConcreteFactory — реализация AbstractFactory, создающая конкретные объекты
 *    AbstractProduct — интерфейс для объектов семейства
 *    ConcreteProduct — конкретная реализация AbstractProduct
 *    Клиент — использует AbstractFactory и AbstractProduct, не зная конкретных классов
 *
 * Как это работает:
 *    AbstractFactory определяет интерфейс для создания всех объектов семейства
 *    ConcreteFactory реализует этот интерфейс, создавая конкретные объекты
 *    AbstractProduct определяет интерфейс для объектов семейства
 *    ConcreteProduct реализует AbstractProduct
 *    Клиент работает с AbstractFactory и AbstractProduct, не зная конкретных классов
 *
 * Преимущества:
 *    Согласованность — все объекты семейства совместимы между собой
 *    Изоляция — клиент не зависит от конкретных классов
 *    Замена семейства — можно легко заменить одно семейство другим
 *    Отсутствие жесткой привязки — нет зависимости от конкретных реализаций
 *    Упрощение кода — клиент работает с единой абстракцией
 * Недостатки:
 *    Избыточность — может создавать объекты, которые не используются
 *    Сложность — добавляет дополнительный уровень абстракции
 *    Изменение интерфейса — изменение AbstractFactory влияет на все ConcreteFactory
 *    Поддержка — сложнее поддерживать, особенно при добавлении новых продуктов
 *    Производительность — дополнительный вызов методов фабрики
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Database Drivers
 *    // Laravel Database Factory - создает драйверы баз данных
 *    $factory = new \Illuminate\Database\Connectors\ConnectionFactory(app());
 *    $connection = $factory->make([
 *        'driver' => 'mysql',
 *        'host' => 'localhost',
 *        'database' => 'test',
 *    ]);
 *    // Или
 *    $connection = $factory->make([
 *        'driver' => 'pgsql',
 *        'host' => 'localhost',
 *        'database' => 'test',
 *    ]);
 * 2. Laravel's Cache Drivers
 *    // Laravel Cache Factory - создает драйверы кэша
 *    $factory = new \Illuminate\Cache\CacheFactory(app());
 *    $store = $factory->make('redis', ['servers' => [...]]);
 *    // Или
 *    $store = $factory->make('memcached', ['servers' => [...]]);
 *    // Или
 *    $store = $factory->make('database', ['table' => 'cache']);
 * 3. Laravel's Queue Drivers
 *    // Laravel Queue Factory - создает драйверы очередей
 *    $factory = new \Illuminate\Queue\Factory(app());
 *    $queue = $factory->make('redis', ['connection' => 'default']);
 *    // Или
 *    $queue = $factory->make('database', ['table' => 'jobs']);
 *    // Или
 *    $queue = $factory->make('sync', []); // синхронная очередь
 * 4. Laravel's Session Drivers
 *    // Laravel Session Factory - создает драйверы сессий
 *    $factory = new \Illuminate\Session\SessionFactory(app());
 *    $session = $factory->make('redis', ['connection' => 'default']);
 *    // Или
 *    $session = $factory->make('database', ['table' => 'sessions']);
 *    // Или
 *    $session = $factory->make('file', ['path' => storage_path('sessions')]);
 * 5. Laravel's Filesystem Adapters
 *    // Laravel Filesystem Factory - создает адаптеры файловых систем
 *    $factory = new \Illuminate\Filesystem\FilesystemManager(app());
 *    $disk = $factory->disk('local');
 *    // Или
 *    $disk = $factory->disk('s3');
 *    // Или
 *    $disk = $factory->disk('ftp');
 * 6. Symfony's Doctrine Connection Factory
 *    // Symfony Doctrine Connection Factory
 *    $factory = new \Doctrine\Bundle\DoctrineBundle\ConnectionFactory();
 *    $connection = $factory->createConnection([
 *        'driver' => 'pdo_mysql',
 *        'host' => 'localhost',
 *        'dbname' => 'test',
 *    ]);
 *    // Или
 *    $connection = $factory->createConnection([
 *        'driver' => 'pdo_pgsql',
 *        'host' => 'localhost',
 *        'dbname' => 'test',
 *    ]);
 * 7. Symfony's Mailer Transport Factory
 *    // Symfony Mailer Transport Factory
 *    $factory = new \Symfony\Component\Mailer\Transport\DsnTransportFactory();
 *    $transport = $factory->create('smtp://localhost:25', []);
 *    // Или
 *    $transport = $factory->create('sendmail://localhost', []);
 *    // Или
 *    $transport = $factory->create('gmail://user:pass@localhost', []);
 * 8. Yii Framework's Db Connection Factory
 *    // Yii Db Connection Factory
 *    $factory = new \yii\db\Connection();
 *    $db = $factory->create([
 *        'dsn' => 'mysql:host=localhost;dbname=test',
 *        'username' => 'root',
 *        'password' => '',
 *    ]);
 *    // Или
 *    $db = $factory->create([
 *        'dsn' => 'pgsql:host=localhost;dbname=test',
 *        'username' => 'postgres',
 *        'password' => '',
 *    ]);
 * 9. CakePHP's Datasource Factory
 *    // CakePHP Datasource Factory
 *    $factory = new \Cake\Database\DriverFactory();
 *    $driver = $factory->create('mysql', [
 *        'host' => 'localhost',
 *        'database' => 'test',
 *    ]);
 *    // Или
 *    $driver = $factory->create('pgsql', [
 *        'host' => 'localhost',
 *        'database' => 'test',
 *    ]);
 * 10. Nette's Database Connection Factory
 *    // Nette Database Connection Factory
 *    $factory = new \Nette\Database\Connection();
 *    $conn = $factory->create('mysql:host=localhost;dbname=test', 'user', 'pass');
 *    // Или
 *    $conn = $factory->create('pgsql:host=localhost;dbname=test', 'user', 'pass');
 * 11. Doctrine's DBAL Connection Factory
 *    // Doctrine DBAL Connection Factory
 *    $factory = new \Doctrine\DBAL\DriverManager();
 *    $conn = $factory->getConnection([
 *        'driver' => 'pdo_mysql',
 *        'host' => 'localhost',
 *        'dbname' => 'test',
 *    ]);
 *    // Или
 *    $conn = $factory->getConnection([
 *        'driver' => 'pdo_pgsql',
 *        'host' => 'localhost',
 *        'dbname' => 'test',
 *    ]);
 * 12. Laravel's Notification Channels
 *    // Laravel Notification Channels Factory
 *    $factory = new \Illuminate\Notifications\ChannelManager(app());
 *    $channel = $factory->driver('mail');
 *    // Или
 *    $channel = $factory->driver('database');
 *    // Или
 *    $channel = $factory->driver('nexmo');
 *
 * Когда полезен:
 *    Семейства объектов — когда нужно создавать несколько взаимосвязанных объектов
 *    Кросс-платформенность — когда нужно поддерживать разные платформы/системы
 *    Замена реализации — когда нужно легко заменять одну реализацию другой
 *    Изоляция — когда нужно изолировать клиент от конкретных классов
 *    Конфигурация — когда конфигурация определяет, какое семейство использовать
 *
 * Разница:
 *      Factory Method      -*-    Abstract Factory(тут)
 *    Создает один продукт      Создает семейство продуктов
 *    Использует наследование   Использует композицию
 *    Один метод создания       Несколько методов создания
 *    Более простой             Более сложный
 *    Для одного продукта	    Для связанных продуктов
 *
 */
try {
    echo "=== Abstract Factory Pattern Example ===\n\n";

    // Пример 1: GUI Factory
    echo "=== GUI Factory Example ===\n";

    $os = 'windows'; // Можно менять на 'macos', но "пингвины" - лучшие )

    $factory = match ($os) {
        'windows' => new WindowsFactory(),
        'macos' => new MacOSFactory(),
        default => throw new \InvalidArgumentException("Unknown OS: $os"),
    };

    $button = $factory->createButton();
    $checkbox = $factory->createCheckbox();
    $textField = $factory->createTextField();

    echo 'Button: ' . $button->paint() . "\n";
    echo 'Checkbox: ' . $checkbox->paint() . "\n";
    echo 'TextField: ' . $textField->paint() . "\n";
    echo 'Button click: ' . $button->onClick() . "\n";
    echo 'Checkbox toggle: ' . $checkbox->toggle() . "\n";
    echo 'TextField text: ' . $textField->getText() . "\n\n";

    // Пример 2: Document Factory
    echo "=== Document Factory Example ===\n";

    $format = 'pdf'; // Можно менять на 'html'

    $factory = match ($format) {
        'pdf' => new PdfDocumentFactory(),
        'html' => new HtmlDocumentFactory(),
        default => throw new \InvalidArgumentException("Unknown format: $format"),
    };

    $paragraph = $factory->createParagraph('Hello, World!');
    $table = $factory->createTable([
        ['Name', 'Age'],
        ['John', '25'],
        ['Jane', '30'],
    ]);
    $image = $factory->createImage('/path/to/image.jpg');

    echo 'Paragraph: ' . $paragraph->render() . "\n";
    echo 'Table: ' . $table->render() . "\n";
    echo 'Image: ' . $image->render() . "\n\n";

    // Пример 3: Payment Factory
    echo "=== Payment Factory Example ===\n";

    $paymentSystem = 'stripe'; // Можно менять на 'paypal'

    $factory = match ($paymentSystem) {
        'stripe' => new StripePaymentFactory(),
        'paypal' => new PayPalPaymentFactory(),
        default => throw new \InvalidArgumentException("Unknown payment system: $paymentSystem"),
    };

    $gateway = $factory->createGateway();
    $logger = $factory->createLogger();
    $validator = $factory->createValidator();

    $logger->log('Starting payment process');

    $paymentData = ['card_token' => 'tok_visa'];
    if ($validator->validate($paymentData)) {
        $result = $gateway->charge(100.00, 'USD');
        $logger->log("Payment {$result->transactionId} completed");
        echo 'Payment result: ' . ($result->success ? 'Success' : 'Failed') . "\n";
    } else {
        $logger->log('Payment validation failed');
        echo "Payment validation failed\n";
    }

    echo "\n=== Currency Factory Example ===\n";

    $currencyFactory = new DefaultCurrencyFactory();

    $usd = $currencyFactory->createCurrency('USD');
    $eur = $currencyFactory->createCurrency('EUR');
    $formatter = $currencyFactory->createFormatter('en_US');

    echo 'USD: ' . $usd->getSymbol() . ' (Rate: ' . $usd->getRate() . ")\n";
    echo 'EUR: ' . $eur->getSymbol() . ' (Rate: ' . $eur->getRate() . ")\n";
    echo 'USD formatted: ' . $formatter->format(1234.56, $usd) . "\n";
    echo 'EUR formatted: ' . $formatter->format(1234.56, $eur) . "\n";
    echo 'Exchange: 100 USD = ' . $currencyFactory->getExchangeRate('USD', 'EUR') . " EUR\n\n";

    // Пример 5: Theme Factory
    echo "=== Theme Factory Example ===\n";

    $theme = 'dark'; // Можно менять на 'light'

    $factory = match ($theme) {
        'light' => new LightThemeFactory(),
        'dark' => new DarkThemeFactory(),
        default => throw new \InvalidArgumentException("Unknown theme: $theme"),
    };

    $buttonStyle = $factory->createButtonStyle();
    $textColor = $factory->createTextColor();
    $bgColor = $factory->createBackgroundColor();

    echo 'Button style: ' . json_encode($buttonStyle->getStyle()) . "\n";
    echo 'Text color: ' . $textColor->getColor() . "\n";
    echo 'Background color: ' . $bgColor->getColor() . "\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/*  Для PHP 8.4           **************************************

// Использование атрибутов для автоматической регистрации фабрик
#[AbstractFactory]
class WidgetFactory
{
    public function createButton(): Button
    {
        return new Button();
    }

    public function createCheckbox(): Checkbox
    {
        return new Checkbox();
    }

    public function createTextField(): TextField
    {
        return new TextField();
    }
}

// Генерация Abstract Factory через атрибуты
class AbstractBuilderFactory
{
    public function build(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstance();

        // Автоматическое создание методов фабрики
        foreach ($reflector->getMethods() as $method) {
            $attributes = $method->getAttributes(FactoryMethod::class);
            foreach ($attributes as $attribute) {
                // @var FactoryMethod $factory
                $factory = $attribute->newInstance();
                $methodName = $factory->name ?? $method->getName();

                // Регистрация метода фабрики
                $this->registerFactory($methodName, $method->invoke($instance));
            }
        }

        return $instance;
    }
}

// Pattern matching для автоматического создания Abstract Factory
public function getFactory(string $type): object
{
    return match ($type) {
        'gui' => match ($os) {
            'windows' => new WindowsFactory(),
            'macos' => new MacOSFactory(),
            'linux' => new LinuxFactory(),
            default => throw new InvalidArgumentException("Unknown OS"),
        },
        'document' => match ($format) {
            'pdf' => new PdfDocumentFactory(),
            'html' => new HtmlDocumentFactory(),
            'docx' => new DocxDocumentFactory(),
            default => throw new InvalidArgumentException("Unknown format"),
        },
        'payment' => match ($system) {
            'stripe' => new StripePaymentFactory(),
            'paypal' => new PayPalPaymentFactory(),
            'braintree' => new BraintreePaymentFactory(),
            default => throw new InvalidArgumentException("Unknown payment system"),
        },
        default => throw new InvalidArgumentException("Unknown factory type"),
    };
}

// Enum для типов Abstract Factory
enum FactoryType: string
{
    case GUI = 'gui';
    case DOCUMENT = 'document';
    case PAYMENT = 'payment';
    case CURRENCY = 'currency';
    case THEME = 'theme';
    case DATABASE = 'database';
    case CACHE = 'cache';
    case SESSION = 'session';
    case MAIL = 'mail';
}

class FactoryFactory
{
    public function create(FactoryType $type, array $config = []): object
    {
        return match ($type) {
            FactoryType::GUI => match ($config['os'] ?? 'windows') {
                'windows' => new WindowsFactory(),
                'macos' => new MacOSFactory(),
                'linux' => new LinuxFactory(),
                default => throw new InvalidArgumentException("Unknown OS"),
            },
            FactoryType::DOCUMENT => match ($config['format'] ?? 'pdf') {
                'pdf' => new PdfDocumentFactory(),
                'html' => new HtmlDocumentFactory(),
                'docx' => new DocxDocumentFactory(),
                default => throw new InvalidArgumentException("Unknown format"),
            },
            FactoryType::PAYMENT => match ($config['system'] ?? 'stripe') {
                'stripe' => new StripePaymentFactory(),
                'paypal' => new PayPalPaymentFactory(),
                'braintree' => new BraintreePaymentFactory(),
                default => throw new InvalidArgumentException("Unknown payment system"),
            },
            FactoryType::CURRENCY => new DefaultCurrencyFactory(),
            FactoryType::THEME => match ($config['theme'] ?? 'light') {
                'light' => new LightThemeFactory(),
                'dark' => new DarkThemeFactory(),
                default => throw new InvalidArgumentException("Unknown theme"),
            },
            FactoryType::DATABASE => match ($config['driver'] ?? 'mysql') {
                'mysql' => new MySqlDatabaseFactory(),
                'pgsql' => new PostgreSqlDatabaseFactory(),
                'sqlite' => new SqliteDatabaseFactory(),
                default => throw new InvalidArgumentException("Unknown database driver"),
            },
            FactoryType::CACHE => match ($config['driver'] ?? 'file') {
                'file' => new FileCacheFactory(),
                'redis' => new RedisCacheFactory(),
                'memcached' => new MemcachedCacheFactory(),
                default => throw new InvalidArgumentException("Unknown cache driver"),
            },
            FactoryType::SESSION => match ($config['driver'] ?? 'file') {
                'file' => new FileSessionFactory(),
                'redis' => new RedisSessionFactory(),
                'database' => new DatabaseSessionFactory(),
                default => throw new InvalidArgumentException("Unknown session driver"),
            },
            FactoryType::MAIL => match ($config['driver'] ?? 'smtp') {
                'smtp' => new SmtpMailFactory(),
                'sendmail' => new SendmailMailFactory(),
                'log' => new LogMailFactory(),
                default => throw new InvalidArgumentException("Unknown mail driver"),
            },
        };
    }
}


   **************************************  */

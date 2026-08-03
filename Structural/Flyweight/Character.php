<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 03.08.2026 - 17:22
 */

namespace Structural\Flyweight;

/**
 * Пример 1: Character Flyweight -
 * Flyweight Interface - общий интерфейс для всех flyweight объектов
 */
interface CharacterFlyweight
{
    public function render(string $font, string $size): string;
}

/**
 * Concrete Flyweight - конкретный flyweight объект
 * Хранит внутреннее состояние (символ), которое не зависит от контекста
 */
class Character implements CharacterFlyweight
{
    private string $char;

    public function __construct(string $char)
    {
        $this->char = $char;
    }

    public function render(string $font, string $size): string
    {
        // В реальном приложении здесь обычно сложная логика рендеринга
        return sprintf('Character: %s, Font: %s, Size: %s', $this->char, $font, $size);
    }

    public function getChar(): string
    {
        return $this->char;
    }
}

/**
 * Unshared Concrete Flyweight - объект, который не может быть разделен
 * Хранит внешнее состояние (контекст), которое уникально для каждого объекта
 * В реальных приложениях, состояние - некий большой, комплексный, массив данных.
 */
class CharacterWithContext implements CharacterFlyweight
{
    private CharacterFlyweight $character;
    private string $context;

    public function __construct(CharacterFlyweight $character, string $context)
    {
        $this->character = $character;
        $this->context = $context;
    }

    public function render(string $font, string $size): string
    {
        return sprintf('%s (Context: %s)', $this->character->render($font, $size), $this->context);
    }

    public function getContext(): string
    {
        return $this->context;
    }
}

/**
 * Flyweight Factory - фабрика, управляющая flyweight объектами
 */
class CharacterFactory
{
    /**
     * @var CharacterFlyweight[]
     */
    private array $pool = [];

    public function get(string $char): CharacterFlyweight
    {
        if (!isset($this->pool[$char])) {
            $this->pool[$char] = new Character($char);
        }

        return $this->pool[$char];
    }

    public function count(): int
    {
        return count($this->pool);
    }

    public function clear(): void
    {
        $this->pool = [];
    }
}

/**
 * Пример 2: Flyweight для работы с форматами файлов
 */
interface FileFormat
{
    public function render(string $content): string;
}

class PdfFormat implements FileFormat
{
    public function render(string $content): string
    {
        return sprintf('PDF: %s', $content);
    }
}

class DocFormat implements FileFormat
{
    public function render(string $content): string
    {
        return sprintf('DOC: %s', $content);
    }
}

class XlsFormat implements FileFormat
{
    public function render(string $content): string
    {
        return sprintf('XLS: %s', $content);
    }
}

class FileFormatFactory
{
    private array $formats = [];

    public function get(string $type): FileFormat
    {
        if (!isset($this->formats[$type])) {
            return match ($type) {
                'pdf' => new PdfFormat(),
                'doc' => new DocFormat(),
                'xls' => new XlsFormat(),
                default => throw new \InvalidArgumentException("Unknown format: $type"),
            };
        }

        return $this->formats[$type];
    }
}

/**
 * Пример 3: Flyweight для работы с валютами
 */
interface CurrencyInterface
{
    public function getCode(): string;
    public function getSymbol(): string;
    public function getRate(): float;
}

class Currency implements CurrencyInterface
{
    private string $code;
    private string $symbol;
    private float $rate;

    public function __construct(string $code, string $symbol, float $rate)
    {
        $this->code = $code;
        $this->symbol = $symbol;
        $this->rate = $rate;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getRate(): float
    {
        return $this->rate;
    }
}

class CurrencyFactory
{
    private array $currencies = [];

    public function get(string $code): CurrencyInterface
    {
        if (!isset($this->currencies[$code])) {
            $this->currencies[$code] = match ($code) {
                'USD' => new Currency('USD', '$', 1.0),
                'EUR' => new Currency('EUR', '€', 0.85),
                'GBP' => new Currency('GBP', '£', 0.75),
                'JPY' => new Currency('JPY', '¥', 110.0),
                'CNY' => new Currency('CNY', '¥', 6.5),
                default => throw new \InvalidArgumentException("Unknown currency: $code"),
            };
        }

        return $this->currencies[$code];
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $fromCurrency = $this->get($from);
        $toCurrency = $this->get($to);

        return $amount * ($toCurrency->getRate() / $fromCurrency->getRate());
    }
}

/**
 * Пример 4: Flyweight для работы с цветами
 */
interface ColorInterface
{
    public function getRgb(): string;
    public function getName(): string;
}

class Color implements ColorInterface
{
    private string $name;
    private string $rgb;

    public function __construct(string $name, string $rgb)
    {
        $this->name = $name;
        $this->rgb = $rgb;
    }

    public function getRgb(): string
    {
        return $this->rgb;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

class ColorFactory
{
    private array $colors = [];

    public function get(string $name): ColorInterface
    {
        if (!isset($this->colors[$name])) {
            $this->colors[$name] = match ($name) {
                'red' => new Color('Red', '#FF0000'),
                'green' => new Color('Green', '#00FF00'),
                'blue' => new Color('Blue', '#0000FF'),
                'white' => new Color('White', '#FFFFFF'),
                'black' => new Color('Black', '#000000'),
                default => throw new \InvalidArgumentException("Unknown color: $name"),
            };
        }

        return $this->colors[$name];
    }
}

/**
 * Пример 5: Flyweight для работы с шрифтами
 */
interface FontInterface
{
    public function getName(): string;
    public function getFamily(): string;
    public function getFileSize(): int;
}

class Font implements FontInterface
{
    private string $name;
    private string $family;
    private int $fileSize;

    public function __construct(string $name, string $family, int $fileSize)
    {
        $this->name = $name;
        $this->family = $family;
        $this->fileSize = $fileSize;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFamily(): string
    {
        return $this->family;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }
}

class FontFactory
{
    private array $fonts = [];

    public function get(string $name): FontInterface
    {
        if (!isset($this->fonts[$name])) {
            $this->fonts[$name] = match ($name) {
                'Arial' => new Font('Arial', 'sans-serif', 120000),
                'Times New Roman' => new Font('Times New Roman', 'serif', 150000),
                'Courier New' => new Font('Courier New', 'monospace', 100000),
                'Verdana' => new Font('Verdana', 'sans-serif', 130000),
                'Georgia' => new Font('Georgia', 'serif', 140000),
                default => throw new \InvalidArgumentException("Unknown font: $name"),
            };
        }

        return $this->fonts[$name];
    }
}

/**
 * Клиентский код
 *
 * Основная идея
 *    Flyweight — интерфейс для объектов, которые могут быть разделены
 *    ConcreteFlyweight — конкретные объекты, которые хранят внутреннее (неразделяемое) состояние
 *    UnsharedConcreteFlyweight — объекты, которые не могут быть разделены
 *    FlyweightFactory — фабрика, которая управляет объектами Flyweight и предоставляет их клиентам
 *
 * Как это работает:
 *    FlyweightFactory управляет объектами Flyweight и предоставляет их клиентам
 *    ConcreteFlyweight хранит внутреннее состояние, которое не зависит от контекста
 *    UnsharedConcreteFlyweight хранит внешнее состояние, которое уникально для каждого объекта
 *    Клиент получает Flyweight объект из фабрики и использует его
 *
 * Преимущества:
 *    Экономия памяти — разделение общих данных между множеством объектов
 *    Улучшение производительности — меньше объектов, меньше памяти
 *    Гибкость — можно легко добавлять новые типы Flyweight
 *    Масштабируемость — позволяет работать с большими объемами данных
 *
 * Недостатки:
 *    Сложность — увеличивает сложность кода
 *    Время доступа — может потребоваться дополнительное время для поиска объектов в фабрике
 *    Жесткая привязка — объекты становятся зависимыми от фабрики
 *    Отладка — сложнее отлаживать, так как объекты разделяются
 *
 *
 * Где используется в фреймворках:
 * 1. Laravel's Collection Macroable
 *    // Laravel Collection macros - flyweight для методов коллекций
 *    Collection::macro('toUpper', function () {
 *        return $this->map(function ($item) {
 *            return strtoupper($item);
 *        });
 *    });
 *    // Использование
 *    $collection = collect(['a', 'b', 'c']);
 *    $result = $collection->toUpper(); // ['A', 'B', 'C']
 * 2. Laravel's Str и Arr Helpers
 *    // Laravel Str и Arr helpers - flyweight для строковых и массивовых операций
 *    use Illuminate\Support\Str;
 *    use Illuminate\Support\Arr;
 *    // Str
 *    $result = Str::of('Hello World')
 *        ->ucfirst()
 *        ->replaceFirst('Hello', 'Goodbye')
 *        ->lower();
 *    // Arr
 *    $result = Arr::add(['name' => 'Desk'], 'price', 100)
 *        ->merge(['price' => 200])
 *        ->except(['price'])
 *        ->all();
 * 3. Symfony's OptionsResolver
 *    // Symfony OptionsResolver - flyweight для конфигураций
 *    $resolver = new OptionsResolver();
 *    $resolver->setRequired('host')
 *        ->setAllowedTypes('host', 'string')
 *        ->setDefault('port', 80)
 *        ->setAllowedTypes('port', 'integer');
 *    // Все запросы используют один экземпляр resolver
 *    $config1 = $resolver->resolve(['host' => 'localhost']);
 *    $config2 = $resolver->resolve(['host' => 'example.com']);
 * 4. Zend Framework's Config
 *    // Zend Config - flyweight для конфигураций
 *    $config = new Config([
 *        'production' => [
 *            'php_settings' => [
 *                'display_errors' => 0,
 *            ],
 *        ],
 *    ]);
 *    // Несколько частей приложения используют одну конфигурацию
 *    $dbConfig = $config->production->php_settings;
 * 5. Yii Framework's Yii::$app
 *    // Yii::$app - глобальный flyweight для приложения
 *    $request = Yii::$app->request;
 *    $response = Yii::$app->response;
 *    $user = Yii::$app->user;
 *    // Все компоненты используют один экземпляр приложения
 * 6. CakePHP's ClassRegistry
 *    // CakePHP ClassRegistry - flyweight для компонентов
 *    $component = ClassRegistry::init('MyComponent');
 *    // Все части приложения используют один экземпляр компонента
 * 7. Nette's Context
 *    // Nette Context - flyweight для сервисов
 *    $service = $this->context->getByType('MyService');
 *    // Несколько частей приложения используют один сервис
 * 8. Doctrine's EntityManager
 *    // Doctrine EntityManager - flyweight для ORM
 *    $entityManager = EntityManager::create($dbParams, $config);
 *    // Несколько репозиториев используют один EntityManager
 *    $userRepository = $entityManager->getRepository('User');
 *    $productRepository = $entityManager->getRepository('Product');
 * 9. Laravel's Service Container
 *    // Laravel Service Container - flyweight для сервисов
 *    $app->singleton(Connection::class, function ($app) {
 *        return new Connection($app['config']['database']);
 *    });
 *    // Все запросы используют один экземпляр Connection
 *    $connection1 = app(Connection::class);
 *    $connection2 = app(Connection::class);
 * 10. Laravel's Facades
 *    // Laravel Facades - flyweight для статического доступа к сервисам
 *    Cache::put('key', 'value', 60);
 *    $value = Cache::get('key');
 *    // Все используют один экземпляр CacheManager
 * 11. Laravel's Macroable Trait
 *    // Laravel Macroable - flyweight для динамических методов
 *    class Str {
 *        use Macroable;
 *    }
 *    // Все экземпляры Str используют одну таблицу макросов
 *    Str::macro('prefix', function ($string, $prefix) {
 *        return $prefix . $string;
 *    });
 *    $result = Str::prefix('world', 'hello ');
 * 12. Symfony's EventDispatcher
 *    // Symfony EventDispatcher - flyweight для событий
 *    $dispatcher = new EventDispatcher();
 *    $dispatcher->addListener('kernel.request', $listener);
 *    // Несколько частей приложения используют один dispatcher
 *    $dispatcher->dispatch(new RequestEvent());
 *
 * Когда полезен:
 *    Большое количество похожих объектов — когда нужно создать множество объектов с общим состоянием
 *    Ограниченная память — когда память является критическим ресурсом
 *    Производительность — когда нужно улучшить производительность за счет уменьшения количества объектов
 *    Кэширование — когда нужно кэшировать часто используемые объекты
 */

try {
    echo "=== Character Flyweight Pattern Example ===\n\n";
    $characterFactory = new CharacterFactory();

    echo "Creating characters...\n";
    $charA = $characterFactory->get('A');
    $charB = $characterFactory->get('B');
    $charC = $characterFactory->get('A'); // Тот же объект, что и $charA

    echo 'Character A: ' . $charA->render('Arial', '12px') . "\n";
    echo 'Character B: ' . $charB->render('Times New Roman', '14px') . "\n";
    echo 'Character C: ' . $charC->render('Verdana', '10px') . "\n";

    echo 'Factory count: ' . $characterFactory->count() . " (should be 2)\n\n";


    echo "=== File Format Flyweight Example ===\n\n";
    $formatFactory = new FileFormatFactory();

    echo "Rendering documents...\n";
    $pdf = $formatFactory->get('pdf');
    $doc = $formatFactory->get('doc');
    $xls = $formatFactory->get('xls');

    echo $pdf->render('Document content 1') . "\n";
    echo $doc->render('Document content 2') . "\n";
    echo $xls->render('Spreadsheet data') . "\n\n";


    echo "=== Currency Flyweight Example ===\n\n";
    $currencyFactory = new CurrencyFactory();

    echo "Currency conversion...\n";
    $usd = $currencyFactory->get('USD');
    $eur = $currencyFactory->get('EUR');
    $gbp = $currencyFactory->get('GBP');

    echo 'USD: ' . $usd->getSymbol() . " (Rate: 1.0)\n";
    echo 'EUR: ' . $eur->getSymbol() . ' (Rate: ' . $eur->getRate() . ")\n";
    echo 'GBP: ' . $gbp->getSymbol() . ' (Rate: ' . $gbp->getRate() . ")\n";

    $amount = 100;
    $converted = $currencyFactory->convert($amount, 'USD', 'EUR');
    echo "$amount USD = $converted EUR\n\n";


    echo "=== Color Flyweight Example ===\n\n";
    $colorFactory = new ColorFactory();

    echo "Color palette...\n";
    $red = $colorFactory->get('red');
    $green = $colorFactory->get('green');
    $blue = $colorFactory->get('blue');
    $white = $colorFactory->get('white');

    echo 'Red: ' . $red->getRgb() . "\n";
    echo 'Green: ' . $green->getRgb() . "\n";
    echo 'Blue: ' . $blue->getRgb() . "\n";
    echo 'White: ' . $white->getRgb() . "\n";
    echo 'Factory count: ' . $colorFactory->count() . "\n\n";


    echo "=== Font Flyweight Example ===\n\n";
    $fontFactory = new FontFactory();

    echo "Font selection...\n";
    $arial = $fontFactory->get('Arial');
    $times = $fontFactory->get('Times New Roman');
    $courier = $fontFactory->get('Courier New');

    echo 'Arial: ' . $arial->getName() . ' (Family: ' . $arial->getFamily() . ")\n";
    echo 'Times New Roman: ' . $times->getName() . ' (Family: ' . $times->getFamily() . ")\n";
    echo 'Courier New: ' . $courier->getName() . ' (Family: ' . $courier->getFamily() . ")\n";
    echo 'Factory count: ' . $fontFactory->count() . "\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/* Для PHP 8.4  ***********************************************

// Использование атрибутов для автоматического создания Flyweight объектов
#[Flyweight]
class Character
{
    private string $char;

    public function __construct(string $char)
    {
        $this->char = $char;
    }

    public function render(string $font, string $size): string
    {
        return sprintf('Character: %s, Font: %s, Size: %s', $this->char, $font, $size);
    }
}

// Генерация Flyweight через атрибуты
class FlyweightBuilder
{
    public function build(string $class, string $key): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstance($key);

        // Автоматическое кэширование
        if (!isset($this->pool[$class][$key])) {
            $this->pool[$class][$key] = $instance;
        }

        return $this->pool[$class][$key];
    }
}

// Pattern matching для автоматического создания Flyweight
public function getFlyweight(string $type, string $key): object
{
    return match ($type) {
    'character' => $this->characterFactory->get($key),
        'format' => $this->formatFactory->get($key),
        'currency' => $this->currencyFactory->get($key),
        'color' => $this->colorFactory->get($key),
        'font' => $this->fontFactory->get($key),
        default => throw new InvalidArgumentException("Unknown flyweight type: $type"),
    };
}

// Enum для типов Flyweight
enum FlyweightType: string
{
    case CHARACTER = 'character';
    case FORMAT = 'format';
    case CURRENCY = 'currency';
    case COLOR = 'color';
    case FONT = 'font';
    case CONFIG = 'config';
    case VALIDATOR = 'validator';
    case LOGGER = 'logger';
}

class FlyweightFactory
{
    public function create(FlyweightType $type, string $key): object
    {
        return match ($type) {
        FlyweightType::CHARACTER => (new CharacterFactory())->get($key),
            FlyweightType::FORMAT => (new FileFormatFactory())->get($key),
            FlyweightType::CURRENCY => (new CurrencyFactory())->get($key),
            FlyweightType::COLOR => (new ColorFactory())->get($key),
            FlyweightType::FONT => (new FontFactory())->get($key),
            FlyweightType::CONFIG => (new ConfigFactory())->get($key),
            FlyweightType::VALIDATOR => (new ValidatorFactory())->get($key),
            FlyweightType::LOGGER => (new LoggerFactory())->get($key),
        };
    }
}

*********************************************** */

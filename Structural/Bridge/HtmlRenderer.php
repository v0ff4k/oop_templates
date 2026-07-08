<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 30.06.2026 - 23:46
 */

namespace Structural\Bridge;

/**
 * Implementor Interface - интерфейс реализации
 */
interface Renderer
{
    public function renderHeader(string $header): string;
    public function renderParagraph(string $text): string;
    public function renderImage(string $url, string $alt): string;
    public function renderFooter(string $content): string;
}

/**
 * Concrete Implementors
 */
class HtmlRenderer implements Renderer
{
    public function renderHeader(string $header): string
    {
        return "<html><head><title>{$header}</title></head><body>\n";
    }

    public function renderParagraph(string $text): string
    {
        return "<p>{$text}</p>\n";
    }

    public function renderImage(string $url, string $alt): string
    {
        return "<img src=\"{$url}\" alt=\"{$alt}\" />\n";
    }

    public function renderFooter(string $content): string
    {
        return "</body></html>\n";
    }
}

class JsonRenderer implements Renderer
{
    public function renderHeader(string $header): string
    {
        return "{\n  \"document\": {\n    \"header\": \"{$header}\",\n";
    }

    public function renderParagraph(string $text): string
    {
        return "    \"paragraph\": \"{$text}\",\n";
    }

    public function renderImage(string $url, string $alt): string
    {
        return "    \"image\": {\"url\": \"{$url}\", \"alt\": \"{$alt}\"},\n";
    }

    public function renderFooter(string $content): string
    {
        return "    \"footer\": \"{$content}\"\n  }\n}\n";
    }
}

class XmlRenderer implements Renderer
{
    public function renderHeader(string $header): string
    {
        return "<?xml version=\"1.0\"?>\n<document>\n  <header>{$header}</header>\n";
    }

    public function renderParagraph(string $text): string
    {
        return "  <paragraph>{$text}</paragraph>\n";
    }

    public function renderImage(string $url, string $alt): string
    {
        return "  <image url=\"{$url}\" alt=\"{$alt}\" />\n";
    }

    public function renderFooter(string $content): string
    {
        return "  <footer>{$content}</footer>\n</document>\n";
    }
}

/**
 * Abstraction - абстракция
 */
abstract class Document
{
    protected Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function setRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    abstract public function generate(): string;
}

/**
 * Refined Abstractions
 */
class Report extends Document
{
    private string $title;
    private string $content;
    private string $conclusion;
    private string $imageUrl;
    private string $imageAlt;

    public function __construct(
        Renderer $renderer,
        string $title,
        string $content,
        string $conclusion,
        string $imageUrl,
        string $imageAlt
    ) {
        parent::__construct($renderer);
        $this->title = $title;
        $this->content = $content;
        $this->conclusion = $conclusion;
        $this->imageUrl = $imageUrl;
        $this->imageAlt = $imageAlt;
    }

    public function generate(): string
    {
        $output = $this->renderer->renderHeader($this->title);
        $output .= $this->renderer->renderParagraph($this->content);
        $output .= $this->renderer->renderImage($this->imageUrl, $this->imageAlt);
        $output .= $this->renderer->renderParagraph($this->conclusion);
        $output .= $this->renderer->renderFooter('End of report');

        return $output;
    }
}

class Article extends Document
{
    private string $headline;
    private array $sections;
    private string $author;
    private string $imageUrl;
    private string $imageAlt;

    public function __construct(
        Renderer $renderer,
        string $headline,
        array $sections,
        string $author,
        string $imageUrl,
        string $imageAlt
    ) {
        parent::__construct($renderer);
        $this->headline = $headline;
        $this->sections = $sections;
        $this->author = $author;
        $this->imageUrl = $imageUrl;
        $this->imageAlt = $imageAlt;
    }

    public function generate(): string
    {
        $output = $this->renderer->renderHeader($this->headline);
        $output .= $this->renderer->renderParagraph("By {$this->author}");

        foreach ($this->sections as $section) {
            $output .= $this->renderer->renderParagraph($section);
        }

        $output .= $this->renderer->renderImage($this->imageUrl, $this->imageAlt);
        $output .= $this->renderer->renderFooter('End of article');

        return $output;
    }
}

/**
 * Пример 2: Система рендеринга графики
 */
interface GraphicsRenderer
{
    public function drawLine(float $x1, float $y1, float $x2, float $y2): string;
    public function drawCircle(float $x, float $y, float $radius): string;
    public function drawRectangle(float $x, float $y, float $width, float $height): string;
    public function fillShape(string $shapeId): string;
}

class SvgRenderer implements GraphicsRenderer
{
    private array $shapes = [];

    public function drawLine(float $x1, float $y1, float $x2, float $y2): string
    {
        $id = 'line_' . count($this->shapes);
        $this->shapes[] = "<line id=\"{$id}\" x1=\"{$x1}\" y1=\"{$y1}\" x2=\"{$x2}\" y2=\"{$y2}\" />";
        return $id;
    }

    public function drawCircle(float $x, float $y, float $radius): string
    {
        $id = 'circle_' . count($this->shapes);
        $this->shapes[] = "<circle id=\"{$id}\" cx=\"{$x}\" cy=\"{$y}\" r=\"{$radius}\" />";
        return $id;
    }

    public function drawRectangle(float $x, float $y, float $width, float $height): string
    {
        $id = 'rect_' . count($this->shapes);
        $this->shapes[] = "<rect id=\"{$id}\" x=\"{$x}\" y=\"{$y}\" width=\"{$width}\" height=\"{$height}\" />";
        return $id;
    }

    public function fillShape(string $shapeId): string
    {
        foreach ($this->shapes as &$shape) {
            if (strpos($shape, "id=\"{$shapeId}\"") !== false) {
                if (strpos($shape, 'circle') !== false) {
                    $shape = str_replace('<circle', '<circle fill="blue"', $shape);
                } elseif (strpos($shape, 'rect') !== false) {
                    $shape = str_replace('<rect', '<rect fill="blue"', $shape);
                }
            }
        }
        return "Filled shape {$shapeId}";
    }

    public function render(): string
    {
        return "<svg xmlns=\"http://www.w3.org/2000/svg\">\n" . implode("\n", $this->shapes) . "\n</svg>";
    }
}

class CanvasRenderer implements GraphicsRenderer
{
    private array $commands = [];

    public function drawLine(float $x1, float $y1, float $x2, float $y2): string
    {
        $id = 'line_' . count($this->commands);
        $this->commands[] = "ctx.moveTo({$x1}, {$y1}); ctx.lineTo({$x2}, {$y2}); ctx.stroke();";
        return $id;
    }

    public function drawCircle(float $x, float $y, float $radius): string
    {
        $id = 'circle_' . count($this->commands);
        $this->commands[] = "ctx.beginPath(); ctx.arc({$x}, {$y}, {$radius}, 0, Math.PI * 2); ctx.stroke();";
        return $id;
    }

    public function drawRectangle(float $x, float $y, float $width, float $height): string
    {
        $id = 'rect_' . count($this->commands);
        $this->commands[] = "ctx.strokeRect({$x}, {$y}, {$width}, {$height});";
        return $id;
    }

    public function fillShape(string $shapeId): string
    {
        foreach ($this->commands as &$command) {
            if (strpos($command, "id=\"{$shapeId}\"") !== false) {
                $command = str_replace('stroke();', 'fill();', $command);
            }
        }
        return "Filled shape {$shapeId}";
    }

    public function render(): string
    {
        return "<canvas>\n" . implode("\n", $this->commands) . "\n</canvas>";
    }
}

/**
 * Abstraction for Graphics
 */
abstract class Shape
{
    protected GraphicsRenderer $renderer;
    protected string $id;

    public function __construct(GraphicsRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function setRenderer(GraphicsRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    abstract public function draw(): void;
    abstract public function fill(): void;
}

/**
 * Refined Abstractions
 */
class Line extends Shape
{
    private float $x1;
    private float $y1;
    private float $x2;
    private float $y2;

    public function __construct(GraphicsRenderer $renderer, float $x1, float $y1, float $x2, float $y2)
    {
        parent::__construct($renderer);
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    public function draw(): void
    {
        $this->id = $this->renderer->drawLine($this->x1, $this->y1, $this->x2, $this->y2);
    }

    public function fill(): void
    {
        $this->renderer->fillShape($this->id);
    }
}

class Circle extends Shape
{
    private float $x;
    private float $y;
    private float $radius;

    public function __construct(GraphicsRenderer $renderer, float $x, float $y, float $radius)
    {
        parent::__construct($renderer);
        $this->x = $x;
        $this->y = $y;
        $this->radius = $radius;
    }

    public function draw(): void
    {
        $this->id = $this->renderer->drawCircle($this->x, $this->y, $this->radius);
    }

    public function fill(): void
    {
        $this->renderer->fillShape($this->id);
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Implementor Interface - определяет интерфейс реализации
 *    Concrete Implementors - реализуют интерфейс разными способами
 *    Abstraction - абстракция, которая использует Implementor
 *    Refined Abstractions - конкретные абстракции, расширяющие базовую
 *    Client - работает с Abstraction, не зная о реализации
 *
 * Преимущества:
 *    Разделение ответственности - абстракция и реализация разделены
 *    Независимое изменение - можно менять и то, и другое отдельно
 *    Сокращение иерархии - вместо n² классов только n + m
 *    Сокрытие реализации - клиенты не зависят от деталей реализации
 *    Гибкость - можно комбинировать любые абстракции с любыми реализациями
 * Недостатки:
 *    Сложность - добавляет дополнительные уровни косвенности
 *    Производительность - может влиять на производительность
 *    Дизайн - требует предварительного планирования
 *    Избыточность - для простых случаев может быть излишним
 *
 * Где используется в фреймворках:
 * 1. Laravel's Queue Drivers
 *    interface QueueDriver
 *    {
 *        public function push(Queueable $job, string $queue = null): void;
 *        public function bulk(array $jobs, string $queue = null): void;
 *        public function later(DateTimeInterface $delay, Queueable $job, string $queue = null): void;
 *    }
 *    class RedisQueue implements QueueDriver { ... }
 *    class DatabaseQueue implements QueueDriver { ... }
 *    class SyncQueue implements QueueDriver { ... }
 *    class BeanstalkdQueue implements QueueDriver { ... }
 *    class QueueManager
 *    {
 *        protected array $drivers = [];
 *        public function driver(string $name): QueueDriver
 *        {
 *            return $this->drivers[$name] ?? $this->resolve($name);
 *        }
 *    }
 *    // Использование
 *    Queue::driver('redis')->push(new SendEmailJob());
 * 2. Laravel's Notification Channels
 *    interface NotificationChannel
 *    {
 *        public function send($notifiable, Notification $notification): void;
 *    }
 *    class DatabaseChannel implements NotificationChannel { ...  }
 *    class MailChannel implements NotificationChannel {  ...  }
 *    class NexmoChannel implements NotificationChannel {  ...  }
 *    class SlackChannel implements NotificationChannel {  ...  }
 *    class NotificationSender
 *    {
 *        protected array $channels = [];
 *        public function send($notifiable, Notification $notification)
 *        {
 *            foreach ($this->channels as $channel) {
 *                if ($notification->via($notifiable) === $channel::class) {
 *                    $channel->send($notifiable, $notification);
 *                }
 *            }
 *        }
 *    }
 * 3. Laravel's Cache Drivers
 *    interface CacheRepository
 *    {
 *        public function get(string $key, mixed $default = null): mixed;
 *        public function put(string $key, mixed $value, DateTimeInterface|int|null $ttl = null): bool;
 *        public function increment(string $key, int $amount = 1): int|false;
 *        public function decrement(string $key, int $amount = 1): int|false;
 *        public function forever(string $key, callable|array $value): bool;
 *        public function forget(string $key): bool;
 *        public function has(string $key): bool;
 *    }
 *    class RedisRepository implements CacheRepository { ... }
 *    class DatabaseRepository implements CacheRepository { ... }
 *    class FileRepository implements CacheRepository { ... }
 *    class MemcachedRepository implements CacheRepository { ... }
 *    class CacheManager
 *    {
 *        protected array $stores = [];
 *        public function store(string $name = null): CacheRepository
 *        {
 *            return $this->stores[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 4. Laravel's Filesystem Disks
 *    interface FilesystemAdapter
 *    {
 *        public function get(string $path): string;
 *        public function put(string $path, string $contents): bool;
 *        public function exists(string $path): bool;
 *        public function delete(string $path): bool;
 *        public function move(string $from, string $to): bool;
 *        public function copy(string $from, string $to): bool;
 *    }
 *    class S3Adapter implements FilesystemAdapter { ... }
 *    class LocalAdapter implements FilesystemAdapter { ... }
 *    class RackspaceAdapter implements FilesystemAdapter { ... }
 *    class AzureAdapter implements FilesystemAdapter { ... }
 *    class FilesystemManager
 *    {
 *        protected array $disks = [];
 *        public function disk(string $name): FilesystemAdapter
 *        {
 *            return $this->disks[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 5. Laravel's Validation Rules
 *    interface Rule
 *    {
 *        public function passes($attribute, $value): bool;
 *        public function message(): string;
 *    }
 *    class Unique implements Rule { ... }
 *    class Exists implements Rule { ... }
 *    class Email implements Rule { ... }
 *    class Regex implements Rule { ... }
 *    class Validator
 *    {
 *    protected array $rules = [];
 *        public function make(array $data, array $rules): Validator
 *        {
 *            // Использует Rule интерфейс
 *        }
 *    }
 * 6. Laravel's Encryption Drivers
 *    interface EncrypterInterface
 *    {
 *        public function encrypt(mixed $value, bool $serialize = true): string;
 *        public function decrypt(string $payload, bool $unserialize = true): mixed;
 *        public function getKey(): string;
 *    }
 *    class OpenSSLEncrypter implements EncrypterInterface { ... }
 *    class SodiumEncrypter implements EncrypterInterface { ... }
 *    class EncryptionManager
 *    {
 *        protected array $encrypters = [];
 *        public function driver(string $name): EncrypterInterface
 *        {
 *            return $this->encrypters[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 7. Laravel's Hashing Drivers
 *    interface Hasher
 *    {
 *        public function info(string $hashedValue): array;
 *        public function make(string $value, array $options = []): string;
 *        public function check(string $value, string $hashedValue, array $options = []): bool;
 *        public function needsRehash(string $hashedValue, array $options = []): bool;
 *    }
 *    class BcryptHasher implements Hasher { ... }
 *    class ArgonHasher implements Hasher { ... }
 *    class SodiumHasher implements Hasher { ... }
 *    class HashManager
 *    {
 *        protected array $hashers = [];
 *        public function driver(string $name): Hasher
 *        {
 *            return $this->hashers[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 8. Laravel's Session Drivers
 *    interface SessionHandlerInterface
 *    {
 *        public function open($savePath, $sessionName): bool;
 *        public function close(): bool;
 *        public function read($sessionId): string;
 *        public function write($sessionId, $data): bool;
 *        public function destroy($sessionId): bool;
 *        public function gc($maxlifetime): bool;
 *    }
 *    class NullSessionHandler implements SessionHandlerInterface { ... }
 *    class FileSessionHandler implements SessionHandlerInterface { ... }
 *    class DatabaseSessionHandler implements SessionHandlerInterface { ... }
 *    class RedisSessionHandler implements SessionHandlerInterface { ... }
 *    class SessionManager
 *    {
 *        protected array $handlers = [];
 *        public function handler(string $name): SessionHandlerInterface
 *        {
 *            return $this->handlers[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 9. Laravel's Mail Transport
 *    interface Transport
 *    {
 *        public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null): void;
 *        public function ping(): bool;
 *    }
 *    class SmtpTransport implements Transport { ... }
 *    class SendmailTransport implements Transport { ... }
 *    class MailgunTransport implements Transport { ...  }
 *    class MandrillTransport implements Transport { ...  }
 *    class TransportManager
 *    {
 *        protected array $transports = [];
 *        public function via(string $name): Transport
 *        {
 *            return $this->transports[$name] ?? $this->resolve($name);
 *        }
 *    }
 * 10. Laravel's Notification Services
 *    interface NotificationService
 *    {
 *        public function send($notifiable, Notification $notification): void;
 *    }
 *    class SmsService implements NotificationService { ... }
 *    class SlackService implements NotificationService { ... }
 *    class DiscordService implements NotificationService { ... }
 *    class PushbulletService implements NotificationService { ... }
 *    class NotificationDispatcher
 *    {
 *        protected array $services = [];
 *        public function via(string $service): NotificationService
 *        {
 *            return $this->services[$service] ?? $this->resolve($service);
 *        }
 *    }
 *
 * Когда полезен:
 *    Кроссплатформенные приложения - разные ОС/устройства
 *    Мультиформатный вывод - разные форматы документов
 *    Графические приложения - разные графические API
 *    Системы хранения - разные хранилища данных
 *    Сети/протоколы - разные сетевые протоколы
 *    Большие иерархии классов - когда иерархия становится слишком глубокой
 */


echo "=== Document Bridge Example(1) ===\n";

// Создаем рендереры
$htmlRenderer = new HtmlRenderer();
$jsonRenderer = new JsonRenderer();
$xmlRenderer = new XmlRenderer();

// Создаем документы с разными рендерерами
$report = new Report(
    $htmlRenderer,
    'Q4 Financial Report',
    'Revenue increased by 25% this quarter...',
    'In conclusion, we are optimistic about future growth...',
    'https://example.com/graph.png',
    'Revenue graph Q4'
);

echo "HTML Report:\n" . $report->generate() . "\n";
//<html><head><title>Q4 Financial Report</title></head><body>
//<p>Revenue increased by 25% this quarter...</p>
//<img src="https://example.com/graph.png" alt="Revenue graph Q4" />
//<p>In conclusion, we are optimistic about future growth...</p>
//</body></html>

$report->setRenderer($jsonRenderer); // Можно менять рендерер динамически
echo "JSON Report:\n" . $report->generate() . "\n";
//{
//    "document": {
//    "header": "Q4 Financial Report",
//    "paragraph": "Revenue increased by 25% this quarter...",
//    "image": {"url": "https://example.com/graph.png", "alt": "Revenue graph Q4"},
//    "paragraph": "In conclusion, we are optimistic about future growth...",
//    "footer": "End of report"
//  }
//}

$report->setRenderer($xmlRenderer);
echo "XML Report:\n" . $report->generate() . "\n";
// < ? xml version="1.0" ? >
//<document>
//  <header>Q4 Financial Report</header>
//  <paragraph>Revenue increased by 25% this quarter...</paragraph>
//  <image url="https://example.com/graph.png" alt="Revenue graph Q4" />
//  <paragraph>In conclusion, we are optimistic about future growth...</paragraph>
//  <footer>End of report</footer>
//</document>


$article = new Article(
    $htmlRenderer,
    'The Future of AI',
    [
        'Artificial Intelligence is transforming industries...',
        'Machine learning algorithms are becoming more sophisticated...',
        'Ethical considerations must be addressed...'
    ],
    'Jane Doe',
    'https://example.com/ai.jpg',
    'AI concept image'
);

echo "\nHTML Article:\n" . $article->generate() . "\n";
//<html><head><title>The Future of AI</title></head><body>
//<p>By Jane Doe</p>
//<p>Artificial Intelligence is transforming industries...</p>
//<p>Machine learning algorithms are becoming more sophisticated...</p>
//<p>Ethical considerations must be addressed...</p>
//<img src="https://example.com/ai.jpg" alt="AI concept image" />
//</body></html>


$article->setRenderer($jsonRenderer);
echo "JSON Article:\n" . $article->generate() . "\n";
//{
//    "document": {
//    "header": "The Future of AI",
//    "paragraph": "By Jane Doe",
//    "paragraph": "Artificial Intelligence is transforming industries...",
//    "paragraph": "Machine learning algorithms are becoming more sophisticated...",
//    "paragraph": "Ethical considerations must be addressed...",
//    "image": {"url": "https://example.com/ai.jpg", "alt": "AI concept image"},
//    "footer": "End of article"
//  }
//}



echo "\n=== Graphics Bridge Example(2) ===\n";

// Создаем рендереры графики
$svgRenderer = new SvgRenderer();
$canvasRenderer = new CanvasRenderer();

// Создаем фигуры
$line = new Line($svgRenderer, 0, 0, 100, 100);
$circle = new Circle($svgRenderer, 50, 50, 30);

$line->draw();
$circle->draw();
$circle->fill();

echo "SVG Rendering:\n" . $svgRenderer->render() . "\n";
//<svg xmlns="http://www.w3.org/2000/svg">
//<line id="line_0" x1="0" y1="0" x2="100" y2="100" />
//<circle fill="blue" id="circle_1" cx="50" cy="50" r="30" />
//</svg>

// Меняем рендерер для тех же фигур
$line->setRenderer($canvasRenderer);
$circle->setRenderer($canvasRenderer);

$line->draw();
$circle->draw();
$circle->fill();

echo "Canvas Rendering:\n" . $canvasRenderer->render() . "\n";
//<canvas>
//ctx.moveTo(0, 0); ctx.lineTo(100, 100); ctx.stroke();
//ctx.beginPath(); ctx.arc(50, 50, 30, 0, Math.PI * 2); ctx.stroke();
//</canvas>



// Альтернативный вариант: (плохо читаемй но по какнонам php8.4)
/********************************************************************************

// Использование атрибутов для определения реализаций
#[Implementation]
class HtmlRenderer implements Renderer {}

// Генерация кода через атрибуты
class BridgeBuilder
{
    public function build(string $abstractionClass, string $implementorClass): object
    {
        $reflector = new ReflectionClass($implementorClass);
        $methods = $reflector->getMethods();

        $bridgeCode = "class GeneratedBridge extends {$abstractionClass} {\n";
        $bridgeCode .= "    protected \$implementor;\n\n";
        $bridgeCode .= "    public function __construct({$implementorClass} \$impl) {\n";
        $bridgeCode .= "        \$this->implementor = \$impl;\n";
        $bridgeCode .= "    }\n\n";

        foreach ($methods as $method) {
            if (!$method->isConstructor() && !$method->isStatic()) {
                $params = $method->getParameters();
                $bridgeCode .= "    public function " . $method->getName() . "(";
                $bridgeCode .= implode(', ', array_map(
                    fn($param) => $param->getType() . ' $' . $param->getName(),
                    $params
                ));
                $bridgeCode .= "): " . ($method->hasReturnType() ? $method->getReturnType() : 'void') . " {\n";
                $bridgeCode .= "        return \$this->implementor->" . $method->getName() . "(";
                $bridgeCode .= implode(', ', array_map(fn($param) => '$' . $param->getName(), $params));
                $bridgeCode .= ");\n";
                $bridgeCode .= "    }\n\n";
            }
        }

        $bridgeCode .= "}\n";

        eval($bridgeCode);
        return new GeneratedBridge(new $implementorClass());
    }
}

// Pattern matching для определения типа рендерера
public function getRenderer(string $type): Renderer
{
    return match ($type) {
        'html' => new HtmlRenderer(),
        'json' => new JsonRenderer(),
        'xml' => new XmlRenderer(),
        default => throw new InvalidArgumentException("Unknown renderer type: $type"),
    };
}

// Enum для типов рендереров
enum RendererType: string
{
    case HTML = 'html';
    case JSON = 'json';
    case XML = 'xml';
    case YAML = 'yaml';
}

class RendererFactory
{
    public function create(RendererType $type): Renderer
    {
        return match ($type) {
            RendererType::HTML => new HtmlRenderer(),
            RendererType::JSON => new JsonRenderer(),
            RendererType::XML => new XmlRenderer(),
            RendererType::YAML => new YamlRenderer(),
        };
    }
}

************************************************************************/

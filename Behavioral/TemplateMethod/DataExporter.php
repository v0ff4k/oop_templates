<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 23.06.2026 - 16:51
 */

namespace Behavioral\TemplateMethod;

use RuntimeException;

/**
 * Пример 1: Abstract Class - определяет шаблонный метод
 *
 * @package Behavioral\TemplateMethod
 */
// Использование атрибутов для определения шагов
#[TemplateMethod]
abstract class DataExporter
{
    /**
     * Шаблонный метод - финальный алгоритм
     * Не может быть переопределен подклассами(final public function)
     */
    final public function export(array $data): string
    {
        $this->preProcess($data);
        $this->format();
        $this->postProcess();
    }

    #[Step(order: 1, description: 'Подготовка данных (фиксированный)')]
    protected function preProcess(array $data): void {}

    #[Step(order: 2, description: 'Форматирование (переопределяемый) + Сохранение (переопределяемый)')]
    abstract protected function format(): void;

    #[Step(order: 3, description: 'Постобработка (фиксированный)')]
    protected function postProcess(): void {}

    /**
     * Фиксированные шаги - не могут быть переопределены
     */
    protected function prepareData(array $data): array
    {
        // Фильтрация, валидация, нормализация
        return array_map([$this, 'normalize'], $data);
    }

    protected function normalize(array $row): array
    {
        return array_map('trim', $row);
    }

    protected function postProcess(string $result): string
    {
        // Добавление метаданных, логирование
        $timestamp = date('Y-m-d H:i:s');

        return "Exported at {$timestamp}\n{$result}";
    }

    /**
     * Абстрактные шаги - должны быть реализованы подклассами
     */
    abstract protected function formatData(array $data): string;
    abstract protected function saveData(string $formatted): string;
}

// "Pattern matching" - для определения шагов, в клиентском коде.
final public function export(array $data): string
{
    return match (true) {
        $this instanceof CsvExporter => $this->exportCsv($data),
        $this instanceof JsonExporter => $this->exportJson($data),
        default => throw new RuntimeException('Unsupported exporter'),
    };
}

/**
 * Использование enum для типов экспорта
 *
 * @package Behavioral\TemplateMethod
 */
enum ExportFormat: string
{
    case CSV = 'csv';
    case JSON = 'json';
    case XML = 'xml';
}

/**
 * Class ExporterFactory - фактори для клиентского кода а не if-else/перебор
 * @package Behavioral\TemplateMethod
 */
class ExporterFactory
{
    public function create(ExportFormat $format): DataExporter
    {
        return match ($format) {
            ExportFormat::CSV => new CsvExporter(),
            ExportFormat::JSON => new JsonExporter(),
            ExportFormat::XML => new XmlExporter(),
        };
    }
}

/**
 * Concrete Class - CSV экспорт
 *
 * @package Behavioral\TemplateMethod
 */
class CsvExporter extends DataExporter
{
    protected function formatData(array $data): string
    {
        $output = [];
        foreach ($data as $row) {
            $output[] = implode(',', $row);
        }

        return implode("\n", $output);
    }

    protected function saveData(string $formatted): string
    {
        // В реальном приложении - сохранение в файл
        return "[CSV]\n" . $formatted;
    }
}

/**
 * Concrete Class - JSON экспорт
 *
 * @package Behavioral\TemplateMethod
 */
class JsonExporter extends DataExporter
{
    protected function formatData(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    protected function saveData(string $formatted): string
    {
        return "[JSON]\n" . $formatted;
    }
}

/**
 * Concrete Class - XML экспорт
 *
 * @package Behavioral\TemplateMethod
 */
class XmlExporter extends DataExporter
{
    protected function formatData(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');

        foreach ($data as $row) {
            $item = $xml->addChild('item');
            foreach ($row as $key => $value) {
                $item->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }

    protected function saveData(string $formatted): string
    {
        return "[XML]\n" . $formatted;
    }
}

/**
 * Пример 2: Построитель запросов
 *
 * @package Behavioral\TemplateMethod
 */
abstract class QueryBuilder
{
    protected array $parts = [];

    final public function build(): string
    {
        $this->select();
        $this->from();
        $this->where();
        $this->groupBy();
        $this->orderBy();

        return $this->assemble();
    }

    abstract protected function select(): void;
    abstract protected function from(): void;

    protected function where(): void
    {
        // Шаг по умолчанию - можно переопределить
        if (isset($this->parts['conditions'])) {
            $this->parts['where'] = 'WHERE ' . implode(' AND ', $this->parts['conditions']);
        }
    }

    protected function groupBy(): void
    {
    }
    protected function orderBy(): void
    {
    }

    private function assemble(): string
    {
        return implode(' ', array_filter($this->parts));
    }
}

/**
 * Class UserQueryBuilder
 *
 * @package Behavioral\TemplateMethod
 */
class UserQueryBuilder extends QueryBuilder
{
    public function __construct()
    {
        $this->parts = [
            'conditions' => ['active = 1']
        ];
    }

    protected function select(): void
    {
        $this->parts['select'] = 'SELECT id, name, email';
    }

    protected function from(): void
    {
        $this->parts['from'] = 'FROM users';
    }

    protected function orderBy(): void
    {
        $this->parts['order'] = 'ORDER BY created_at DESC';
    }
}

/**
 * Пример 3: Валидация форм
 *
 * @package Behavioral\TemplateMethod
 */
abstract class FormValidator
{
    final public function validate(array $data): array
    {
        $errors = [];

        // Шаг 1: Обязательные поля (фиксированный)
        $required = $this->getRequiredFields();
        foreach ($required as $field) {
            if (empty($data[$field] ?? '')) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        // Шаг 2: Правила валидации (переопределяемые)
        foreach ($data as $field => $value) {
            $rules = $this->getRulesForField($field);
            foreach ($rules as $rule) {
                if (!$this->checkRule($value, $rule)) {
                    $errors[] = "Field '{$field}' failed rule: {$rule}";
                }
            }
        }

        return $this->finalizeValidation($errors);
    }

    abstract protected function getRequiredFields(): array;
    abstract protected function getRulesForField(string $field): array;

    protected function checkRule(mixed $value, string $rule): bool
    {
        return match ($rule) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'numeric' => is_numeric($value),
            'required' => !empty($value),
            default => true,
        };
    }

    protected function finalizeValidation(array $errors): array
    {
        return $errors;
    }
}

/**
 * Class UserRegistrationValidator
 *
 * @package Behavioral\TemplateMethod
 */
class UserRegistrationValidator extends FormValidator
{
    protected function getRequiredFields(): array
    {
        return ['username', 'email', 'password'];
    }

    protected function getRulesForField(string $field): array
    {
        return match ($field) {
            'email' => ['email', 'required'],
            'password' => ['required', 'min:8'],
            default => ['required'],
        };
    }

    protected function checkRule(mixed $value, string $rule): bool
    {
        return match ([$rule, $value]) {
            ['min:8', $value] => strlen($value) >= 8,
            default => parent::checkRule($value, $rule),
        };
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Abstract Class (DataExporter) - определяет шаблонный метод export()
 *    Шаблонный метод - финальный метод, который нельзя переопределить
 *    Шаги алгоритма - комбинация фиксированных и абстрактных методов
 *    Подклассы - реализуют абстрактные шаги по-своему
 *
 * Преимущества:
 *    Инверсия управления - базовый класс вызывает методы подклассов
 *    Избегание дублирования - общая логика в базовом классе
 *    Четкая структура - алгоритм определен один раз
 *    Расширяемость - легко добавить новые форматы/варианты
 *
 * Недостатки:
 *    Ограничение гибкости - нельзя изменить порядок шагов
 *    Наследование вместо композиции - жесткая связь
 *    Хрупкий базовый класс - изменения в шаблоне влияют на все подклассы
 *
 * Где используется в фреймворках:
 * 1. Laravel's Artisan Commands
 *    use Illuminate\Console\Command;
 *    class SendEmailsCommand extends Command
 *    {
 *        protected $signature = 'emails:send {user}';
 *        // Шаблонный метод handle() определяет алгоритм
 *        public function handle()
 *        {
 *            // Шаг 1: Подготовка (фиксирован)
 *            $this->info('Starting email sending...');
 *            // Шаг 2: Логика (переопределяемая)
 *            $this->sendEmails();
 *            // Шаг 3: Завершение (фиксированное)
 *            $this->info('Email sending completed!');
 *        }
 *        protected function sendEmails(): void
 *        {
 *            // Конкретная реализация
 *        }
 *    }
 * 2. Symfony's Bundle Lifecycle
 *    use Symfony\Component\HttpKernel\Bundle\Bundle;
 *    class AppBundle extends Bundle
 *    {
 *        // Шаблонный метод boot() определяет жизненный цикл
 *        public function boot()
 *        {
 *            parent::boot(); // Фиксированный шаг
 *            // Переопределяемый шаг
 *            $this->customBootLogic();
 *        }
 *        protected function customBootLogic(): void
 *        {
 *             // Логика инициализации бандла
 *        }
 *    }
 * 3. PHPUnit's TestCase
 *    use PHPUnit\Framework\TestCase;
 *    class MyTest extends TestCase
 *    {
 *        // Шаблонный метод run() определяет алгоритм теста
 *        public function run(): void
 *        {
 *            $this->setUp();    // Фиксированный шаг
 *            $this->testMethod(); // Переопределяемый
 *            $this->tearDown();   // Фиксированный шаг
 *        }
 *        protected function setUp(): void
 *        {
 *            // Подготовка тестового окружения
 *        }
 *        protected function testMethod(): void
 *        {
 *            // Конкретный тест
 *        }
 *    }
 * 4. Laravel's Mailable
 *    use Illuminate\Bus\Queueable;
 *    use Illuminate\Mail\Mailable;
 *    class WelcomeEmail extends Mailable
 *    {
 *        // Шаблонный метод build() определяет структуру письма
 *        public function build()
 *        {
 *            // Фиксированные шаги
 *            $this->subject('Welcome!');
 *            $this->view('emails.welcome');
 *            // Переопределяемый шаг
 *            $this->configureMessage();
 *            return $this;
 *        }
 *        protected function configureMessage(): void
 *        {
 *            // Дополнительная конфигурация
 *        }
 *    }
 * 5. Django's Class-Based Views (аналог в Python, но похоже)
 *    # В PHP аналог - Laravel's Controllers
 *    class UserController extends Controller
 *    {
 *        // Шаблонный метод handle() определяет обработку запроса
 *        public function handle(Request $request)
 *        {
 *            $this->validateRequest($request); // Фиксированный
 *            $this->processRequest($request);  // Переопределяемый
 *            $this->sendResponse();           // Фиксированный
 *        }
 *        protected function processRequest(Request $request): void
 *        {
 *            // Конкретная логика
 *        }
 *    }
 * 6. Laravel's Resource Collections
 *    use Illuminate\Http\Resources\Json\JsonResource;
 *    class UserResource extends JsonResource
 *    {
 *        // Шаблонный метод toArray() определяет структуру ответа
 *        public function toArray($request)
 *        {
 *            // Фиксированные поля
 *            $data = [
 *                'id' => $this->id,
 *                'name' => $this->name,
 *            ];
 *            // Переопределяемые поля
 *            $data += $this->additionalFields();
 *            return $data;
 *        }
 *        protected function additionalFields(): array
 *        {
 *            return [];
 *        }
 *    }
 *
 * Когда полезен:
 *    Фреймворки - lifecycle hooks, обработчики событий
 *    Экспорт/импорт - различные форматы данных
 *    Валидация - разные правила для разных форм
 *    Построение запросов - SQL, API запросы
 *    Обработка документов - парсинг разных форматов
 *    Алгоритмы с вариациями - сортировка, поиск с разными стратегиями
 *
 */

echo "=== Data Exporter Example ===\n";
$data = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
];

echo "\nCSV Export:\n";
echo (new CsvExporter())->export($data);

echo "\n\nJSON Export:\n";
echo (new JsonExporter())->export($data);

echo "\n\nXML Export:\n";
echo (new XmlExporter())->export($data);

echo "\n=== Query Builder Example ===\n";
$queryBuilder = new UserQueryBuilder();
echo "SQL Query:\n" . $queryBuilder->build();

echo "\n=== Form Validation Example ===\n";
$validator = new UserRegistrationValidator();
$formData = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
];

$errors = $validator->validate($formData);
if (empty($errors)) {
    echo "Form is valid!\n";
} else {
    echo "Validation errors:\n" . implode("\n", $errors) . "\n";
}

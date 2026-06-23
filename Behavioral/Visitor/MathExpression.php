<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 23.06.2026 - 21:54
 */

namespace Behavioral\Visitor;

/**
 * Visitor Interface - интерфейс посетителя
 */
interface Visitor
{
    public function visitNumber(Number $number): float;
    public function visitAddition(Addition $addition): float;
    public function visitMultiplication(Multiplication $multiplication): float;
}

/**
 * Element Interface - интерфейс элемента, принимающего посетителя
 */
interface Expression
{
    public function accept(Visitor $visitor): float;
}

/**
 * Concrete Elements
 */
class Number implements Expression
{
    public function __construct(private float $value)
    {
    }

    public function accept(Visitor $visitor): float
    {
        return $visitor->visitNumber($this);
    }

    public function getValue(): float
    {
        return $this->value;
    }
}

class Addition implements Expression
{
    /** @var Expression[] */
    private array $operands;

    public function __construct(Expression ...$operands)
    {
        $this->operands = $operands;
    }

    public function accept(Visitor $visitor): float
    {
        return $visitor->visitAddition($this);
    }

    /**
     * @return Expression[]
     */
    public function getOperands(): array
    {
        return $this->operands;
    }
}

class Multiplication implements Expression
{
    /** @var Expression[] */
    private array $factors;

    public function __construct(Expression ...$factors)
    {
        $this->factors = $factors;
    }

    public function accept(Visitor $visitor): float
    {
        return $visitor->visitMultiplication($this);
    }

    /**
     * @return Expression[]
     */
    public function getFactors(): array
    {
        return $this->factors;
    }
}

/**
 * Concrete Visitors
 */
class EvaluationVisitor implements Visitor
{
    public function visitNumber(Number $number): float
    {
        return $number->getValue();
    }

    public function visitAddition(Addition $addition): float
    {
        $result = 0;
        foreach ($addition->getOperands() as $operand) {
            $result += $operand->accept($this);
        }
        return $result;
    }

    public function visitMultiplication(Multiplication $multiplication): float
    {
        $result = 1;
        foreach ($multiplication->getFactors() as $factor) {
            $result *= $factor->accept($this);
        }
        return $result;
    }
}

class StringifyVisitor implements Visitor
{
    public function visitNumber(Number $number): float
    {
        echo (string) $number->getValue();
        return 0; // Не используется
    }

    public function visitAddition(Addition $addition): float
    {
        $parts = [];
        foreach ($addition->getOperands() as $operand) {
            $operand->accept($this);
            $parts[] = '';
        }
        echo '(' . implode(' + ', $parts) . ')';
        return 0;
    }

    public function visitMultiplication(Multiplication $multiplication): float
    {
        $parts = [];
        foreach ($multiplication->getFactors() as $factor) {
            $factor->accept($this);
            $parts[] = '';
        }
        echo '(' . implode(' * ', $parts) . ')';
        return 0;
    }
}

class DerivativeVisitor implements Visitor
{
    public function visitNumber(Number $number): float
    {
        return 0; // Производная константы = 0
    }

    public function visitAddition(Addition $addition): float
    {
        $derivatives = [];
        foreach ($addition->getOperands() as $operand) {
            $derivatives[] = $operand->accept($this);
        }
        return array_sum($derivatives);
    }

    public function visitMultiplication(Multiplication $multiplication): float
    {
        $factors = $multiplication->getFactors();
        if (count($factors) === 2) {
            [$u, $v] = $factors;
            // (u*v)' = u'*v + u*v'
            return $u->accept($this) * $v->accept($this)
                + $u->accept($this) * $v->accept($this);
        }
        throw new \RuntimeException('Only binary multiplication supported');
    }
}

/**
 * Пример 2: Документы с разными типами
 */
interface DocumentElement
{
    public function accept(Visitor $visitor): void;
}

class Text implements DocumentElement
{
    public function __construct(private string $content)
    {
    }

    public function accept(Visitor $visitor): void
    {
        $visitor->visitText($this);
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

class Image implements DocumentElement
{
    public function __construct(private string $url, private int $width, private int $height)
    {
    }

    public function accept(Visitor $visitor): void
    {
        $visitor->visitImage($this);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
    public function getWidth(): int
    {
        return $this->width;
    }
    public function getHeight(): int
    {
        return $this->height;
    }
}

class Table implements DocumentElement
{
    /** @var array<array<string>> */
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function accept(Visitor $visitor): void
    {
        $visitor->visitTable($this);
    }

    /** @return array<array<string>> */
    public function getRows(): array
    {
        return $this->rows;
    }
}

interface DocumentVisitor
{
    public function visitText(Text $text): void;
    public function visitImage(Image $image): void;
    public function visitTable(Table $table): void;
}

class HtmlExportVisitor implements DocumentVisitor
{
    private string $output = '';

    public function visitText(Text $text): void
    {
        $this->output .= htmlspecialchars($text->getContent());
    }

    public function visitImage(Image $image): void
    {
        $this->output .= sprintf(
            '<img src="%s" width="%d" height="%d" />',
            htmlspecialchars($image->getUrl()),
            $image->getWidth(),
            $image->getHeight()
        );
    }

    public function visitTable(Table $table): void
    {
        $this->output .= '<table>';
        foreach ($table->getRows() as $row) {
            $this->output .= '<tr>';
            foreach ($row as $cell) {
                $this->output .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $this->output .= '</tr>';
        }
        $this->output .= '</table>';
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}

/**
 * Пример 3: Дерево каталога
 */
interface CatalogElement
{
    public function accept(CatalogVisitor $visitor): void;
}

class Product implements CatalogElement
{
    public function __construct(
        private string $id,
        private string $name,
        private float $price
    ) {
    }

    public function accept(CatalogVisitor $visitor): void
    {
        $visitor->visitProduct($this);
    }

    public function getId(): string
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getPrice(): float
    {
        return $this->price;
    }
}

class Category implements CatalogElement
{
    /** @var CatalogElement[] */
    private array $children;

    public function __construct(private string $name, ...$children)
    {
        $this->children = $children;
    }

    public function accept(CatalogVisitor $visitor): void
    {
        $visitor->visitCategory($this);
        foreach ($this->children as $child) {
            $child->accept($visitor);
        }
    }

    /** @return CatalogElement[] */
    public function getChildren(): array
    {
        return $this->children;
    }
    public function getName(): string
    {
        return $this->name;
    }
}

interface CatalogVisitor
{
    public function visitProduct(Product $product): void;
    public function visitCategory(Category $category): void;
}

class PriceCalculationVisitor implements CatalogVisitor
{
    private float $total = 0;

    public function visitProduct(Product $product): void
    {
        $this->total += $product->getPrice();
    }

    public function visitCategory(Category $category): void
    {
        // Категория не добавляет цену, только дочерние элементы
    }

    public function getTotal(): float
    {
        return $this->total;
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Visitor Interface - определяет методы visit*() для каждого типа элемента
 *    Element Interface - определяет метод accept() для принятия посетителя
 *    Concrete Elements - реализуют accept() вызывая соответствующий метод посетителя
 *    Concrete Visitors - реализуют операции для каждого типа элементов
 *    Object Structure - содержит элементы и позволяет обходить их
 *
 * Преимущества:
 *    Добавление новых операций - без изменения классов элементов
 *    Разделение логики - операции отделены от структур данных
 *    Гибкость - легко добавлять новые посетители
 *    Инкапсуляция - изменение одной операции не влияет на другие
 *
 * Недостатки:
 *    Изменение элементов - добавление нового типа элемента требует изменения всех посетителей
 *    Наследование - элементы должны знать о посетителях
 *    Производительность - двойная диспетчеризация (accept + visit)
 *    Сложность - может быть избыточным для простых случаев
 *
 * Где используется в фреймворках:
 * 1. Laravel's Visitor Pattern in Forms
 *    use Illuminate\Routing\Router;
 *    class FormVisitor
 *    {
 *        public function visitTextInput(TextInput $input): void
 *        {
 *            // Обработка текстового поля
 *        }
 *        public function visitSelectInput(SelectInput $input): void
 *        {
 *            // Обработка выпадающего списка
 *        }
 *    }
 *    // Обход всех элементов формы
 *    foreach ($formElements as $element) {
 *        $element->accept($visitor);
 *    }
 * 2. Symfony's Workflow Visitor
 *    use Symfony\Component\Workflow\Registry;
 *    class WorkflowVisitor implements VisitorInterface
 *    {
 *        public function visitPlace(Place $place): void
 *        {
 *            // Логирование мест
 *        }
 *        public function visitTransition(Transition $transition): void
 *        {
 *            // Проверка переходов
 *        }
 *    }
 *    // Обход всех элементов workflow
 *    $workflow->accept($visitor);
 * 3. PhpParser's NodeVisitor
 *    use PhpParser\NodeVisitorAbstract;
 *    use PhpParser\Node;
 *    class MyNodeVisitor extends NodeVisitorAbstract
 *    {
 *        public function enterNode(Node $node): void
 *        {
 *            // Посещение узла AST
 *            if ($node instanceof Node\Expr\FuncCall) {
 *                // Обработка вызовов функций
 *            }
 *        }
 *    }
 *    // Парсер вызывает посетителя для каждого узла
 *    $parser->parse($code);
 *    $traverser->addVisitor(new MyNodeVisitor());
 *    $traverser->traverse($ast);
 * 4. Laravel's Query Builder Visitor
 *    use Illuminate\Database\Query\Builder;
 *    class QueryVisitor
 *    {
 *        public function visitSelect(Select $select): void
 *        {
 *            // Обработка SELECT
 *        }
 *        public function visitWhere(Where $where): void
 *        {
 *            // Обработка WHERE
 *        }
 *    }
 *    // Обход компонентов запроса
 *    $query->accept($visitor);
 * 5. Doctrine's AST Visitor
 *    use Doctrine\ORM\Query\AST;
 *    class DqlVisitor extends \Doctrine\ORM\Query\AST\Visitor
 *    {
 *        public function visitSelectStatement(AST\SelectStatement $selectStatement): void
 *        {
 *            // Обработка SELECT
 *        }
 *        public function visitPathExpression(AST\PathExpression $pathExpression): void
 *        {
 *            // Обработка путей
 *        }
 *    }
 *    // Парсер DQL вызывает посетителя
 *    $parser->parse($dql);
 *    $parser->getAST()->accept($visitor);
 * 6. Laravel's Event Subscriber
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
 *        public function onOrderPaid(OrderPaid $event): void
 *        {
 *            // Обработка оплаты
 *        }
 *    }
 *    // Диспетчер событий вызывает подписчика
 *    $dispatcher->addSubscriber(new OrderSubscriber());
 *
 * Когда полезен:
 *    Компиляторы - обход AST, анализ кода
 *    Документы - экспорт в разные форматы
 *    Графические редакторы - операции над фигурами
 *    Бизнес-логика - сложные объектные структуры
 *    ETL процессы - преобразование данных(Transform), из выборка(Extract) и загрузки(Load)
 *    Валидация - разные правила для разных типов
 *
 */

echo "=== Expression Visitor Example1 ===\n";

// (2 + 3) * 4
$expression = new Multiplication(
    new Addition(new Number(2), new Number(3)),
    new Number(4)
);

echo 'Expression evaluation: ';
echo $expression->accept(new EvaluationVisitor()) . "\n";

echo 'Expression as string: ';
$expression->accept(new StringifyVisitor());
echo "\n";

echo 'Derivative d/dx((2+3)*4): ';
echo $expression->accept(new DerivativeVisitor()) . "\n";


echo "\n=== Document Visitor Example2 ===\n";

$document = [
    new Text('Hello World'),
    new Image('logo.png', 100, 100),
    new Table([
        ['Name', 'Email'],
        ['Alice', 'alice@example.com'],
        ['Bob', 'bob@example.com'],
    ])
];

$visitor = new HtmlExportVisitor();
foreach ($document as $element) {
    $element->accept($visitor);
}

echo "HTML Output:\n" . $visitor->getOutput() . "\n";


echo "\n=== Catalog Visitor Example3 ===\n";

// Электроника > Телефоны > iPhone ($999), Samsung ($899)
// Электроника > Ноутбуки > MacBook ($1999), Dell ($1499)
// Одежда > Футболки ($29), Джинсы ($89)

$iphone = new Product('p1', 'iPhone', 999);
$samsung = new Product('p2', 'Samsung', 899);
$macbook = new Product('p3', 'MacBook', 1999);
$dell = new Product('p4', 'Dell', 1499);
$tShirt = new Product('p5', 'T-Shirt', 29);
$jeans = new Product('p6', 'Jeans', 89);

$phones = new Category('Телефоны', $iphone, $samsung);
$laptops = new Category('Ноутбуки', $macbook, $dell);
$clothes = new Category('Одежда', $tShirt, $jeans);

$electronics = new Category('Электроника', $phones, $laptops);
$clothing = new Category('Одежда', $clothes);
$root = new Category('Каталог', $electronics, $clothing);

$priceVisitor = new PriceCalculationVisitor();
$root->accept($priceVisitor);
echo 'Total catalog price: $' . $priceVisitor->getTotal() . "\n";

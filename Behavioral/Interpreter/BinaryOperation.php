<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 13.06.2026 - 20:37
 */

namespace Behavioral\Interpreter;

use DivisionByZeroError;
use SplStack;

/**
 * Interface Expression
 * Абстрактное выражение - базовый интерфейс для всех узлов синтаксического дерева
 *
 * @package Behavioral\Interpreter
 */
interface Expression
{
    public function interpret(Context $context): float;
}

/**
 * Class Context
 * Контекст содержит глобальную информацию, доступную всем выражениям
 *
 * @package Behavioral\Interpreter
 */
class Context
{
    private array $variables = [];

    public function setVariable(string $name, float $value): void
    {
        $this->variables[$name] = $value;
    }

    public function getVariable(string $name): float
    {
        return $this->variables[$name] ?? 0.0;
    }
}

/**
 * Class Value
 * Терминальное выражение - представляет константы и переменные
 *
 * @package Behavioral\Interpreter
 */
class Value implements Expression
{
    /**
     * Value constructor.
     * @param float $value
     */
    public function __construct(private float $value)
    {
    }

    /**
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        return $this->value;
    }
}

/**
 * Class Variable
 * Терминальное выражение для переменных
 *
 * @package Behavioral\Interpreter
 */
class Variable implements Expression
{
    /**
     * Variable constructor.
     * @param string $name
     */
    public function __construct(private string $name)
    {
    }

    /**
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        return $context->getVariable($this->name);
    }
}

/**
 * Class BinaryOperation
 * Нетерминальное выражение для бинарных операций
 *
 * @package Behavioral\Interpreter
 */
abstract class BinaryOperation implements Expression
{
    /**
     * BinaryOperation constructor.
     * @param Expression $left
     * @param Expression $right
     */
    public function __construct(
        protected Expression $left,
        protected Expression $right
    ) {
    }
}

/**
 * Class Add
 * Конкретная операция сложения
 *
 * @package Behavioral\Interpreter
 */
class Add extends BinaryOperation
{
    /**
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        return $this->left->interpret($context) + $this->right->interpret($context);
    }
}

/**
 * Class Subtract
 * Конкретная операция вычитания
 *
 * @package Behavioral\Interpreter
 */
class Subtract extends BinaryOperation
{
    /**
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        return $this->left->interpret($context) - $this->right->interpret($context);
    }
}

/**
 * Class Multiply
 * Конкретная операция умножения
 *
 * @package Behavioral\Interpreter
 */
class Multiply extends BinaryOperation
{
    /**
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        return $this->left->interpret($context) * $this->right->interpret($context);
    }
}

/**
 * Class Divide
 * Конкретная операция деления
 *
 * @package Behavioral\Interpreter
 */
class Divide extends BinaryOperation
{
    // Макс точностью, может быть: опасно/нехорошо/долго
    private const PHP_EPS = PHP_FLOAT_EPSILON; // 2.2204460492503E-16 (by OS)
    // Сравниваем с точностью до 0.001
    private const EPSILON = 0.001; // most realistic

    /**
     * Через fdiv() функцию с конечной проверкой, на бесконечность, медленее.
     *
     * @param Context $context
     * @return float
     */
    public function interpretPhp84(Context $context): float
    {
        $leftValue = $this->left->interpret($context);
        $rightValue = $this->right->interpret($context);

        // fdiv безопасно делит на 0.0 и возвращает INF или NAN
        $result = fdiv($leftValue, $rightValue);

        if (is_infinite($result) || is_nan($result)) {
            throw new DivisionByZeroError('Division by zero');
        }

        return $result;
    }

    /**
     * OldyStyle старая проверка, до расчета
     *
     * @param Context $context
     * @return float
     */
    public function interpret(Context $context): float
    {
        $rightValue = $this->right->interpret($context);
        // (bccomp($rightValue, '0.00', 2) === 0)  // on MONEY.(or BcMath\Number - OOP style)
        if (abs($rightValue) < self::EPSILON) {
            throw new DivisionByZeroError('Division by zero');
        }

        return ($this->left->interpret($context) / $rightValue);
    }
}

/**
 * Class ExpressionParser
 * Парсер арифметических выражений(Здесь больше для теста)
 * ВНИМАНИЕ, написан под постфиксную нотацию (обратную польскую запись), где операторы идут после чисел.
 *
 * @package Behavioral\Interpreter
 */
class ExpressionParser
{
    /**
     * @param string $expression
     * @return Expression
     * @todo right-left priority check!
     */
    public static function parse(string $expression): Expression
    {
        $tokens = preg_split('/\s+/', trim($expression));
        $stack = new SplStack();

        foreach ($tokens as $token) {
            // 1. Убираем return из цикла. Помещаем объекты в стек.
            $stack->push(match ($token) {
                '+' => new Add($stack->pop(), $stack->pop()),
                '-' => new Subtract($stack->pop(), $stack->pop()),
                '*' => new Multiply($stack->pop(), $stack->pop()),
                '/' => new Divide($stack->pop(), $stack->pop()),
                default => is_numeric($token)
                    ? new Value((float)$token)
                    : new Variable($token)
            });
            // OR real hardCORE:
            // $this instanceof Add =>
            // $this instanceof Subtract =>
            // $this instanceof Multiply =>
            // ...
        }

        return $stack->pop();
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Контекст хранит переменные и их значения
 *    Expression - базовый интерфейс для всех выражений
 *    Value и Variable - терминальные выражения (листья дерева)
 *    BinaryOperation - абстрактный класс для бинарных операций
 *    Add, Subtract, Multiply, Divide - конкретные операции
 *    ExpressionParser - строит синтаксическое дерево из строки
 *
 * Преимущества:
 *    Расширяемость - легко добавлять новые операции
 *    Читаемость - грамматика явно представлена в коде
 *    Переиспользование - выражения можно кешировать и переиспользовать
 *    Тестируемость - каждый компонент можно тестировать отдельно(OOP style =)
 * Недостатки:
 *    Сложность - для простых случаев может быть избыточным
 *    Производительность - создание объектов для каждого выражения
 *    Ограниченность - лучше подходит для простых грамматик
 *
 *
 * Где используется(в ключевых фреймворках):
 * 1. Laravel - Query Builder
 *    // Пример: where('age', '>', 18)
 *    $query->where('age', '>', 18);
 *    // Грамматика: [where, column, operator, value]
 * 2. Doctrine ORM
 *    // DQL запросы: "SELECT u FROM User u WHERE u.age > 18"
 *    // Парсятся в объектное представление
 * 3. Symfony ExpressionLanguage
 *    $language = new ExpressionLanguage();
 *    $language->evaluate('x + y * 2', ['x' => 10, 'y' => 5]);
 *    // Использует интерпретатор для безопасного выполнения выражений
 * 4. Twig Templates
 *    {% if user.age > 18 %} {{ user.name }} {% endif %}
 *    // Интерпретирует шаблонные выражения
 * 5. PHPMailer / SwiftMailer
 *    // Парсинг email-адресов и правил маршрутизации
 *
 * Когда полезен:
 *    Формулы и калькуляторы (финансовые расчеты, научные вычисления)
 *    Правила бизнес-логики (страховые полисы, кредитные - скОринГ)
 *    Фильтрация и поиск (польз-е фильтры в админках)
 *    Конфигурации (настр-е правила в CMS)
 *    Безопасное выполнение польз-о кода (песочница)
 */

$context = new Context();
$context->setVariable('x', 10);
$context->setVariable('y', 5);

// Выражение:  [ (x + y) * 2 ] - [ x / y ]
$expression = new Subtract(
    new Multiply(
        new Add(new Variable('x'), new Variable('y')),
        new Value(2)
    ),
    new Divide(new Variable('x'), new Variable('y'))
);

$result = $expression->interpret($context);
echo "Expression result: {$result}\n"; // Output: Expression result: 28

// Альтернативно через парсер(test)  постфиксная нотация (обратная польская запись), где операторы идут после чисел.
$parsed = ExpressionParser::parse('x y + 2 * x y / -');
$parsedResult = $parsed->interpret($context);
echo "Parsed result: {$parsedResult}\n"; // Output: Parsed result: 28

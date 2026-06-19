<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.06.2026 - 13:19
 */

namespace Behavioral\State;

/**
 * Interface OrderState
 * State Interface - интерфейс состояния
 * Пример 1: Состояние статусов заказа.
 *
 * @package Behavioral\State
 */
interface OrderState
{
    public function next(OrderContext $context): void;
    public function prev(OrderContext $context): void;
    public function getStatus(): string;
    public function canNext(): bool;
    public function canPrev(): bool;
}

/**
 * Class OrderContext
 * Context - контекст заказа
 *
 * @package Behavioral\State
 */
class OrderContext
{
    private OrderState $state;
    private string $orderId;
    private array $items = [];

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
        $this->state = new State\Draft();
    }

    /**
     * @param \Behavioral\State\OrderState $state
     */
    public function setState(OrderState $state): void
    {
        $this->state = $state;
    }

    /**
     * @return OrderState
     */
    public function getState(): OrderState
    {
        return $this->state;
    }

    /**
     * Next
     */
    public function next(): void
    {
        $this->state->next($this);
    }

    /**
     * Prev
     */
    public function prev(): void
    {
        $this->state->prev($this);
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        return $this->state->getStatus();
    }

    /**
     * addItem
     * @param string $item
     * @param float $price
     */
    public function addItem(string $item, float $price): void
    {
        $this->items[] = ['item' => $item, 'price' => $price];
    }

    /**
     * addItems
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * getTotal
     * @return float
     */
    public function getTotal(): float
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + $item['price'], 0);
    }
}


/**
 * Concrete States
 *
 * example located in Behavioral\State\State
 */
namespace Behavioral\State\State;

use Behavioral\State\OrderContext;

/**
 * Class Draft
 * Draft State - черновок
 *
 * @package Behavioral\State\State
 */
class Draft implements OrderState
{
    public function next(OrderContext $context): void
    {
        if ($context->getTotal() > 0) {
            $context->setState(new Processing());
        } else {
            throw new \RuntimeException('Cannot process empty order');
        }
    }

    /**
     * @param OrderContext $context
     */
    public function prev(OrderContext $context): void
    {
        // Нельзя вернуться назад из черновка
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'draft';
    }

    /**
     * @return bool
     */
    public function canNext(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPrev(): bool
    {
        return false;
    }
}

/**
 * Class Processing
 * Processing State - обработка
 *
 * @package Behavioral\State\State
 */
class Processing implements OrderState
{
    /**
     * @param OrderContext $context
     */
    public function next(OrderContext $context): void
    {
        $context->setState(new Paid());
    }

    /**
     * @param OrderContext $context
     */
    public function prev(OrderContext $context): void
    {
        $context->setState(new Draft());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'processing';
    }

    /**
     * @return bool
     */
    public function canNext(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPrev(): bool
    {
        return true;
    }
}

/**
 * Class Paid
 * Paid State - оплачен
 *
 * @package Behavioral\State\State
 */
class Paid implements OrderState
{
    /**
     * @param OrderContext $context
     */
    public function next(OrderContext $context): void
    {
        $context->setState(new Shipped());
    }

    /**
     * @param OrderContext $context
     */
    public function prev(OrderContext $context): void
    {
        $context->setState(new Processing());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'paid';
    }

    /**
     * @return bool
     */
    public function canNext(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPrev(): bool
    {
        return true;
    }
}

/**
 * Class Shipped
 * Shipped State - отправлен
 *
 * @package Behavioral\State\State
 */
class Shipped implements OrderState
{
    /**
     * @param OrderContext $context
     */
    public function next(OrderContext $context): void
    {
        $context->setState(new Delivered());
    }

    /**
     * @param OrderContext $context
     */
    public function prev(OrderContext $context): void
    {
        $context->setState(new Paid());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'shipped';
    }

    /**
     * @return bool
     */
    public function canNext(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canPrev(): bool
    {
        return true;
    }
}

/**
 * Class Delivered
 * Delivered State - доставлен (финальное)
 *
 * @package Behavioral\State\State
 */
class Delivered implements OrderState
{
    /**
     * @param OrderContext $context
     */
    public function next(OrderContext $context): void
    {
        // Нельзя перейти дальше
    }

    /**
     * @param OrderContext $context
     */
    public function prev(OrderContext $context): void
    {
        $context->setState(new Shipped());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'delivered';
    }

    /**
     * @return bool
     */
    public function canNext(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canPrev(): bool
    {
        return true;
    }
}

/**
 * Class DocumentWorkflow
 * Пример 2: Государственный автомат (Workflow)
 *
 * @package Behavioral\State\State
 */
class DocumentWorkflow
{
    private string $status = 'draft';
    private array $allowedTransitions = [
        'draft' => ['review', 'published'],
        'review' => ['published', 'draft'],
        'published' => [], // Финальное состояние
    ];

    /**
     * @param string $newStatus
     * @return bool
     */
    public function canTransitTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions[$this->status] ?? []);
    }

    /**
     * @param string $newStatus
     */
    public function transitTo(string $newStatus): void
    {
        if (!$this->canTransitTo($newStatus)) {
            throw new \RuntimeException("Cannot transition from {$this->status} to {$newStatus}");
        }
        $this->status = $newStatus;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}

/**
 * Interface PaymentState
 * Пример 3: Состояние процесса оплаты
 *
 * @package Behavioral\State\State
 */
interface PaymentState
{
    public function process(PaymentContext $context): void;
    public function cancel(PaymentContext $context): void;
    public function getStatus(): string;
}

/**
 * Class PaymentContext
 *
 * @package Behavioral\State\State
 */
class PaymentContext
{
    private PaymentState $state;
    private float $amount;
    private bool $completed = false;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
        $this->state = new States\Pending();
    }

    /**
     * @param PaymentState $state
     */
    public function setState(PaymentState $state): void
    {
        $this->state = $state;
    }

    /**
     *
     */
    public function process(): void
    {
        $this->state->process($this);
    }

    /**
     *
     */
    public function cancel(): void
    {
        $this->state->cancel($this);
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * complete
     */
    public function complete(): void
    {
        $this->completed = true;
    }
}



/**
 * Payment States
 *
 * example located in Behavioral\State\Payment\States
 */
namespace Behavioral\State\Payment\States;

use Behavioral\State\PaymentContext;
use Behavioral\State\PaymentState;

/**
 * Class Pending
 * @package Behavioral\State\Payment\States
 */
class Pending implements PaymentState
{
    /**
     * @param PaymentContext $context
     */
    public function process(PaymentContext $context): void
    {
        echo "Processing payment of {$context->getAmount()}...\n";
        $context->setState(new Processing());
    }

    /**
     * @param PaymentContext $context
     */
    public function cancel(PaymentContext $context): void
    {
        echo "Payment cancelled\n";
        $context->setState(new Cancelled());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'pending';
    }
}

/**
 * Class Processing
 *
 * @package Behavioral\State\Payment\States
 */
class Processing implements PaymentState
{
    /**
     * @param PaymentContext $context
     */
    public function process(PaymentContext $context): void
    {
        echo "Payment of {$context->getAmount()} completed!\n";
        $context->complete();
        $context->setState(new Completed());
    }

    /**
     * @param PaymentContext $context
     */
    public function cancel(PaymentContext $context): void
    {
        echo "Cancelling processing payment...\n";
        $context->setState(new Cancelled());
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'processing';
    }
}

/**
 * Class Completed
 *
 * @package Behavioral\State\Payment\States
 */
class Completed implements PaymentState
{
    /**
     * @param PaymentContext $context
     */
    public function process(PaymentContext $context): void
    {
        // Ничего не делаем, платеж уже завершен
    }

    /**
     * @param PaymentContext $context
     */
    public function cancel(PaymentContext $context): void
    {
        // Нельзя отменить завершенный платеж
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'completed';
    }
}

/**
 * Class Cancelled
 *
 * @package Behavioral\State\Payment\States
 */
class Cancelled implements PaymentState
{
    /**
     * @param PaymentContext $context
     */
    public function process(PaymentContext $context): void
    {
        // Нельзя обработать отмененный платеж
    }

    /**
     * @param PaymentContext $context
     */
    public function cancel(PaymentContext $context): void
    {
        // Уже отменен
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'cancelled';
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Context (OrderContext) - содержит ссылку на текущее состояние
 *    State (OrderState) - интерфейс состояний
 *    ConcreteState (Draft, Processing, etc.) - реализуют поведение для каждого состояния
 *    При изменении - Context делегирует вызовы текущему состоянию
 *
 * Преимущества:
 *    Локализация состояний - логика состояния изолирована в отдельных классах
 *    Избегание условных операторов - нет больших switch/if в Context
 *    Легкое добавление состояний - новый класс без изменения существующего
 *    Явное управление состояниями - четко определены переходы между состояниями
 * Недостатки:
 *    Много классов - каждый состояние требует своего класса
 *    Сложность - может быть избыточным для простых случаев
 *    Состояние как данные - состояние хранится в объекте, а не в данных
 *
 * Где используется в фреймворках:
 * 1. Laravel's State Machine
 *    use Spatie\StateMachine\StateMachine;
 *    $stateMachine = new StateMachine($order, [
 *        'draft' => new DraftState(),
 *        'processing' => new ProcessingState(),
 *        'completed' => new CompletedState(),
 *    ]);
 *    $stateMachine->transition('process'); // Переход из draft в processing
 * 2. Symfony's Workflow Component
 *    use Symfony\Component\Workflow\Workflow;
 *    use Symfony\Component\Workflow\Registry;
 *    $workflow = new Workflow($definition, $supportStrategy);
 *    $workflow->can($order, 'to_process'); // Проверка возможности перехода
 *    $workflow->apply($order, 'process'); // Применение перехода
 * 3. Doctrine's Lifecycle Callbacks (double spaces for doc support)
 *    /* *
 *     * @ORM\Entity
 *     * @ORM\Table(name="orders")
 *     * /
 *    class Order
 *    {
 *        / * *
 *         * @ORM\Column(name="status", type="string", length=255)
 *         * @var string
 *         * /
 *        private $status = 'draft';
 *        / * *
 *         * @ORM\PreUpdate
 *         * /
 *        public function preUpdate()
 *        {
 *              // Логика при изменении состояния
 *        }
 *    }
 * 4. Laravel's Eloquent Model Events
 *    class Order extends Model
 *    {
 *        protected $dispatchesEvents = [
 *            'created' => OrderCreated::class,
 *            'updated' => OrderUpdated::class,
 *        ];
 *        protected static function booted()
 *        {
 *            static::creating(function ($order) {
 *                $order->status = 'draft';
 *            });
 *        }
 *    }
 * 5. Yii2 Workflow
 *    use yii\workflow\WorkflowBehavior;
 *    class Order extends \yii\db\ActiveRecord
 *    {
 *        public function behaviors()
 *        {
 *            return [
 *                'workflow' => [
 *                    'class' => WorkflowBehavior::class,
 *                    'workflowName' => 'orderWorkflow',
 *                ],
 *            ];
 *        }
 *    }
 *    $order->workflowApply('process'); // Переход в состояние processing
 * 6. Drupal's State Machine
 *    use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
 *    $workflow = $this->workflowManager->createInstance('order', $configuration);
 *    $workflow->apply($order, 'process'); // Переход между состояниями
 *
 * Когда полезен:
 *    Транзакции - банковские операции, заказы
 *    Workflow - документооборот, процессы согласования
 *    Игры - состояния игрока (жив, мертв, в бою)
 *    UI - состояния формы (валидная, невалидная, отправлена)
 *    Сетевые протоколы - состояния соединения
 *    Планировщики задач - состояния задач (ожидание, выполнение, завершена)
 *
 */
echo "=== Order State Machine ===\n";
$order = new OrderContext('ORDER-001');
echo "Initial status: {$order->getStatus()}\n"; // draft

$order->addItem('Book', 29.99);
$order->next();
echo "After addItem and next: {$order->getStatus()}\n"; // processing

$order->next();
echo "After next: {$order->getStatus()}\n"; // paid

$order->next();
echo "After next: {$order->getStatus()}\n"; // shipped

$order->next();
echo "After next: {$order->getStatus()}\n"; // delivered

// Попытка перейти дальше (нельзя)
try {
    $order->next();
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Document Workflow ===\n";
$doc = new DocumentWorkflow();
echo "Document status: {$doc->getStatus()}\n"; // draft

$doc->transitTo('review');
echo "Document status: {$doc->getStatus()}\n"; // review

$doc->transitTo('published');
echo "Document status: {$doc->getStatus()}\n"; // published

// Попытка вернуться назад (нельзя)
try {
    $doc->transitTo('draft');
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Payment State Machine ===\n";
$payment = new PaymentContext(99.99);
$payment->process();
echo "Payment status: " . $payment->getState()->getStatus() . "\n"; // processing

$payment->process();
echo "Payment completed? " . ($payment->isCompleted() ? 'Yes' : 'No') . "\n";

<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.06.2026 - 15:51
 */

namespace Behavioral\Strategy;

use RuntimeException;

/**
 * Interface PaymentStrategy
 * Strategy Interface - интерфейс стратегии
 *
 * @package Behavioral\Strategy
 */
interface PaymentStrategy
{
    public function pay(float $amount): PaymentResult;

    public function getMethodName(): string;
}

/**
 * Class PaymentResult
 * Payment Result - результат оплаты
 *
 * @package Behavioral\Strategy
 */
class PaymentResult
{
    public function __construct(
        private bool    $success,
        private string  $message,
        private ?string $transactionId = null
    ) {
    }

    /**
     * isSuccess
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * getMessage
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * getTransactionId
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}

/**
 * Class CreditCardStrategy
 * Concrete Strategy - оплата картой
 *
 * @package Behavioral\Strategy
 */
class CreditCardStrategy implements PaymentStrategy
{
    public function __construct(
        private string $cardNumber,
        private string $expiryDate,
        private string $cvv
    ) {
    }

    /**
     * pay
     * @param float $amount
     * @return PaymentResult
     * @throws \Random\RandomException
     */
    public function pay(float $amount): PaymentResult
    {
        // Здесь должна быть реальная обработка платежа
        // Для примера -  пока ТОЛЬКО имитация
        if (strlen($this->cardNumber) < 16) {
            return new PaymentResult(false, 'Invalid card number');
        }

        return new PaymentResult(
            true,
            'Payment successful',
            'txn_' . bin2hex(random_bytes(8))
        );
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return 'Credit Card';
    }
}

/**
 * Class PayPalStrategy
 * Concrete Strategy - оплата через PayPal
 *
 * @package Behavioral\Strategy
 */
class PayPalStrategy implements PaymentStrategy
{
    public function __construct(private string $email, private string $password)
    {
    }

    /**
     * pay
     * @param float $amount
     * @return PaymentResult
     */
    public function pay(float $amount): PaymentResult
    {
        // Имитация PayPal API
        return new PaymentResult(
            true,
            'PayPal payment completed',
            'paypal_' . time()
        );
    }

    /**
     * getMethodName
     * @return string
     */
    public function getMethodName(): string
    {
        return 'PayPal';
    }
}

/**
 * Class CashOnDeliveryStrategy
 * Concrete Strategy - оплата при получении
 *
 * @package Behavioral\Strategy
 */
class CashOnDeliveryStrategy implements PaymentStrategy
{
    public function pay(float $amount): PaymentResult
    {
        return new PaymentResult(
            true,
            "Cash on delivery for \${$amount} selected"
        );
    }

    public function getMethodName(): string
    {
        return 'Cash on Delivery';
    }
}

/**
 * Class BitcoinStrategy
 * Concrete Strategy - биткоин
 *
 * @package Behavioral\Strategy
 */
class BitcoinStrategy implements PaymentStrategy
{
    public function __construct(private string $walletAddress)
    {
    }

    /**
     * pay
     * @param float $amount
     * @return PaymentResult
     */
    public function pay(float $amount): PaymentResult
    {
        $btcAmount = $amount / 50000; // Тестовый курс, не придирайся!
        return new PaymentResult(
            true,
            "Bitcoin payment of {$btcAmount} BTC to {$this->walletAddress}",
            'btc_' . uniqid()
        );
    }

    /**
     * getMethodName
     * @return string
     */
    public function getMethodName(): string
    {
        return 'Bitcoin';
    }
}

/**
 * Class ShoppingCart
 * Context - корзина покупок
 *
 * @package Behavioral\Strategy
 */
class ShoppingCart
{
    /** @var array<string, array> */
    private array $items = [];
    private PaymentStrategy $paymentStrategy;

    public function __construct(
        private string $currency = 'USD'
    ) {
    }

    /**
     * addItem
     * @param string $productId
     * @param string $name
     * @param float $price
     * @param int $quantity
     */
    public function addItem(string $productId, string $name, float $price, int $quantity): void
    {
        $this->items[$productId] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }

    /**
     * setPaymentStrategy
     * @param PaymentStrategy $strategy
     */
    public function setPaymentStrategy(PaymentStrategy $strategy): void
    {
        $this->paymentStrategy = $strategy;
    }

    /**
     * checkout
     * @return PaymentResult
     */
    public function checkout(): PaymentResult
    {
        $total = $this->calculateTotal();
        echo "Checkout: Total amount: \${$total}\n";

        if (!isset($this->paymentStrategy)) {
            throw new RuntimeException('No payment strategy selected');
        }

        return $this->paymentStrategy->pay($total);
    }

    /**
     * calculateTotal
     * @return float
     */
    public function calculateTotal(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * getItems
     * @return array[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}


/**
 * Interface SortStrategy
 * Пример 2: Алгоритмы сортировки
 *
 * @package Behavioral\Strategy
 */
interface SortStrategy
{
    public function sort(array $data): array;

    public function getName(): string;
}

/**
 * Class BubbleSortStrategy
 * @package Behavioral\Strategy
 */
class BubbleSortStrategy implements SortStrategy
{
    public function sort(array $data): array
    {
        $n = count($data);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($data[$j] > $data[$j + 1]) {
                    $temp = $data[$j];
                    $data[$j] = $data[$j + 1];
                    $data[$j + 1] = $temp;
                }
            }
        }
        return $data;
    }

    public function getName(): string
    {
        return 'Bubble Sort';
    }
}

class QuickSortStrategy implements SortStrategy
{
    public function sort(array $data): array
    {
        if (count($data)) {
            $pivot = $data[array_key_first($data)];
            $left = $right = [];

            foreach ($data as $item) {
                if ($item < $pivot) {
                    $left[] = $item;
                } elseif ($item > $pivot) {
                    $right[] = $item;
                }
            }

            // Recursively sort and merge
            return array_merge(
                $this->sort($left),
                [$pivot],
                $this->sort($right)
            );
        }

        return $data;
    }

    public function getName(): string
    {
        return 'Quick Sort';
    }
}


/**
 * Клиентский код
 */

// Framework usage examples
$laravelCollection = collect([3, 1, 4, 1, 5, 9, 2, 6]);
$laravelSorted = $laravelCollection->sort(); // Uses Quicksort under the hood

$symfonyCollection = new \Symfony\Component\PropertyAccess\PropertyAccess();
$values = [3, 1, 4, 1, 5, 9, 2, 6];
$values = $symfonyCollection->sort($values); // Uses Quicksort in implementation

// PHP Standard Library usage
$phpArray = [3, 1, 4, 1, 5, 9, 2, 6];
$phpArray = $phpArray; // Quicksort is used in PHP's internal sort functions

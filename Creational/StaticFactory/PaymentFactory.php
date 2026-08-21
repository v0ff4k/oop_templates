<?php

declare(strict_types=1);


/**
 * Created by pSom.
 * User: 9r00+
 * at: 21.08.2026 - 1:18
 */

namespace Creational\StaticFactory;

/**
 * Product Interface - общий интерфейс для всех продуктов
 */
interface PaymentMethod
{
    public function process(float $amount, string $currency): bool;
    public function getFee(): float;
}

/**
 * Concrete Product 1 - Credit Card Payment
 */
final class CreditCardPayment implements PaymentMethod
{
    private string $cardNumber;
    private string $expiryDate;
    private string $cvv;

    private function __construct(string $cardNumber, string $expiryDate, string $cvv)
    {
        $this->cardNumber = $cardNumber;
        $this->expiryDate = $expiryDate;
        $this->cvv = $cvv;
    }

    public function process(float $amount, string $currency): bool
    {
        // Логика обработки кредитной карты
        echo "Processing $amount $currency via Credit Card ({$this->maskCard()})\n";

        return true;
    }

    public function getFee(): float
    {
        return 0.025; // 2.5% комиссия
    }

    private function maskCard(): string
    {
        return str_repeat('*', strlen($this->cardNumber) - 4) .
            substr($this->cardNumber, -4);
    }

    /**
     * Static Factory Method
     */
    public static function create(string $cardNumber, string $expiryDate, string $cvv): self
    {
        // Валидация данных карты
        if (!self::validateCard($cardNumber)) {
            throw new \InvalidArgumentException('Invalid card number');
        }

        return new self($cardNumber, $expiryDate, $cvv);
    }

    private static function validateCard(string $cardNumber): bool
    {
        // Упрощенная валидация
        return preg_match('/^\d{16}$/', $cardNumber) &&
            self::luhnCheck($cardNumber);
    }

    private static function luhnCheck(string $number): bool
    {
        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$number[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 == 0;
    }
}

/**
 * Concrete Product 2 - PayPal Payment
 */
final class PayPalPayment implements PaymentMethod
{
    private string $email;
    private string $password;

    private function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function process(float $amount, string $currency): bool
    {
        // Логика обработки PayPal
        echo "Processing $amount $currency via PayPal ({$this->email})\n";

        return true;
    }

    public function getFee(): float
    {
        return 0.035; // 3.5% комиссия
    }

    /**
     * Static Factory Method
     */
    public static function create(string $email, string $password): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }

        return new self($email, $password);
    }
}

/**
 * Concrete Product 3 - Crypto Payment
 */
final class CryptoPayment implements PaymentMethod
{
    private string $walletAddress;
    private string $cryptoType;

    private function __construct(string $walletAddress, string $cryptoType)
    {
        $this->walletAddress = $walletAddress;
        $this->cryptoType = $cryptoType;
    }

    public function process(float $amount, string $currency): bool
    {
        // Логика обработки крипты
        echo "Processing $amount $currency via {$this->cryptoType} ({$this->maskWallet()})\n";
        return true;
    }

    public function getFee(): float
    {
        return 0.01; // 1% комиссия
    }

    /**
     * Static Factory Method
     */
    public static function create(string $walletAddress, string $cryptoType): self
    {
        $validTypes = ['BTC', 'ETH', 'USDT', 'SOL'];

        if (!in_array(strtoupper($cryptoType), $validTypes)) {
            throw new \InvalidArgumentException('Unsupported crypto type');
        }

        return new self($walletAddress, strtoupper($cryptoType));
    }

    private function maskWallet(): string
    {
        return substr($this->walletAddress, 0, 8) . '...' .
            substr($this->walletAddress, -8);
    }
}

/**
 * Static Factory - основной класс с фабричным методом
 */
final class PaymentFactory
{
    /**
     * Статический метод для создания платежных методов
     */
    public static function create(string $type, array $config): PaymentMethod
    {
        return match (strtolower($type)) {
            'credit_card', 'card' => CreditCardPayment::create(
                $config['card_number'],
                $config['expiry_date'],
                $config['cvv']
            ),
            'paypal', 'pp' => PayPalPayment::create(
                $config['email'],
                $config['password']
            ),
            'crypto', 'bitcoin', 'ethereum', 'usdt', 'sol' => CryptoPayment::create(
                $config['wallet_address'],
                $config['crypto_type'] ?? 'BTC'
            ),
            default => throw new \InvalidArgumentException("Unknown payment type: $type"),
        };
    }
}

/**
 * Пример использования
 *
 * Ключевая особенность
 *    Один класс предоставляет статический метод factory() или build()
 *    Продукты часто являются вложенными классами или реализуют общий интерфейс
 *    Клиент работает с фабричным методом класса продукта
 *
 * Преимущества StaticFactory:
 *    Простота — нет необходимости в отдельном классе фабрики
 *    Инкапсуляция — логика создания внутри продукта
 *    Читаемость — явный статический метод create()
 *    Гибкость — можно добавлять валидацию в фабричный метод
 *    Тестируемость — проще мокировать статические методы
 * Недостатки:
 *    Нарушение Single Responsibility — продукт знает о создании
 *    Сложность тестирования — статические методы труднее тестировать
 *    Жесткая привязка — продукт знает о своих зависимостях
 *    Отсутствие наследования — final классы не могут быть расширены
 *    Сложность конфигурации — все конфигурации через параметры метода
 *
 * Примеры из фреймворков:
 * 1. Laravel's Notification Channels
 *    // StaticFactory для каналов уведомлений
 *    $notification->via($notifiable);
 *    // Внутри Notification::via() - статический метод
 *    public function via($notifiable) { return ['mail', 'database']; }
 *    // Каждый канал реализует свой статический метод
 *    MailChannel::create($notification)->send($notifiable);
 * 2. Laravel's Model Factories
 *    // StaticFactory для создания моделей
 *    $factory->define(User::class, function (Faker $faker) {
 *        return [ 'name' => $faker->name, 'email' => $faker->unique()->safeEmail, ];
 *    });
 *    // Использование статического метода
 *    $user = factory(User::class)->create();
 * 3. Symfony's Response Factory
 *    // StaticFactory для HTTP ответов
 *    $response = Response::create('Hello World', 200, ['Content-Type' => 'text/html']);
 *    // Возвращает Symfony\Component\HttpFoundation\Response
 *    $jsonResponse = JsonResponse::create(['status' => 'success']);
 *    // Возвращает Symfony\Component\HttpFoundation\JsonResponse
 * 4. Laravel's Validation Rules
 *    // StaticFactory для валидационных правил
 *    $rule = Required::rule();
 *    // Возвращает экземпляр Required
 *    $rule = Email::rule();
 *    // Возвращает экземпляр Email
 * 5. Laravel's Notification Mailable
 *    // StaticFactory для писем
 *    $mailable = new OrderShipped($order); // Создание через конструктор
 *    $mailable = OrderShipped::create($order); // Или через статический метод
 * 6. Laravel's Event Classes
 *    // StaticFactory для событий
 *    $event = new OrderShipped($order); // Создание через конструктор
 *    $event = OrderShipped::dispatch($order); // Или через статический метод
 * 7. Laravel's Job Classes
 *    // StaticFactory для задач очереди
 *    $job = new ProcessOrder($order); // Создание через конструктор
 *    $job = ProcessOrder::dispatch($order); // Или через статический метод
 * 8. Laravel's Mailables
 *    // StaticFactory для отправки писем
 *    Mail::to($user)->send(new OrderShipped($order));
 *    OrderShipped::to($user)->send($order); // Или через статический метод
 * 9. Laravel's Notification Channels (Advanced)
 *    // StaticFactory для кастомных каналов
 *    class DatabaseChannel {
 *        public static function create(Notification $notification): self {
 *            return new self($notification);
 *        }
 *        public function send($notifiable, Notification $notification): void {
 *            // Отправка в базу данных
 *        }
 *    }
 * 10. Laravel's Broadcasting Channel
 *    // StaticFactory для вещания
 *    class BroadcastChannel {
 *        public static function create(Notification $notification): self {
 *            return new self($notification);
 *        }
 *        public function send($notifiable, Notification $notification): void {
 *            // Вещание через Pusher
 *        }
 *    }
 *
 *
 * Когда использовать StaticFactory:
 *    Когда продукты простые и не требуют сложной конфигурации
 *    Когда нужна простая фабрика без дополнительных классов
 *    Когда продукты не наследуются (final классы)
 *    Когда логика создания тесно связана с продуктом
 *    Когда нужен быстрый доступ к фабричному методу
 *
 * Когда НЕ использовать:
 *    Когда продукты сложные и требуют разных конфигураций
 *    Когда нужна гибкость (FactoryMethod лучше)
 *    Когда нужно создавать семейства объектов (AbstractFactory лучше)
 *    Когда продукты наследуются (нарушает принцип подстановки)
 *
 *
 *
 * Когда выбирать нужный Factory:
 *
 * Выбирайте StaticFactory когда:
 *    Продукты простые и не требуют сложной конфигурации
 *    Нужна быстрая и простая фабрика
 *    Продукты не наследуются (final классы)
 *    Логика создания тесно связана с продуктом
 *
 * Выбирайте SimpleFactory когда:
 *    Нужна отдельная фабрика для нескольких продуктов
 *    Продукты могут быть наследуемыми
 *    Нужна централизованная логика создания
 *
 * Выбирайте FactoryMethod когда:
 *    Нужна иерархия классов с фабричными методами
 *    Требуется гибкость и расширяемость
 *    Следует Open/Closed Principle
 *
 * Выбирайте AbstractFactory когда:
 *    Нужно создавать семейства связанных объектов
 *    Требуется абстракция над фабриками
 *    Необходима независимость от способа создания
 *
 *
 *         Сравнение с другими фабриками:
 * StaticFactory = Класс продукта предоставляет create()
 *   Статический метод в продукте
 *     Продукты часто final
 *       Простая реализация.
 * SimpleFactory = Отдельный класс с create()
 *   НЕстатический метод в фабрике
 *     Продукты могут быть наследуемыми
 *       Простая реализация.
 * FactoryMethod = Базовый класс с create()
 *   Виртуальный метод в базовом классе
 *     Продукты могут быть наследуемыми
 *       Гибкая реализация.
 * AbstractFactory = Иерархия фабрик
 *   Семейство методов create()
 *     Продукты создаются фабриками
 *       Гибкая реализация.
 *
 */

try {
    echo "=== Static Factory Pattern Example ===\n\n";

    // Создание платежа через кредитную карту
    $cardPayment = PaymentFactory::create('credit_card', [
        'card_number' => '4111111111111111',
        'expiry_date' => '12/2025',
        'cvv' => '123'
    ]);
    $cardPayment->process(100.00, 'USD');
    echo 'Fee: ' . ($cardPayment->getFee() * 100) . "%\n\n";

    // Создание платежа через PayPal
    $paypalPayment = PaymentFactory::create('paypal', [
        'email' => 'user@example.com',
        'password' => 'securepassword'
    ]);
    $paypalPayment->process(50.00, 'EUR');
    echo 'Fee: ' . ($paypalPayment->getFee() * 100) . "%\n\n";

    // Создание платежа через крипту
    $cryptoPayment = PaymentFactory::create('crypto', [
        'wallet_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
        'crypto_type' => 'BTC'
    ]);
    $cryptoPayment->process(0.1, 'BTC');
    echo 'Fee: ' . ($cryptoPayment->getFee() * 100) . "%\n\n";

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}


/* пример на php8.4 *************************************


// Использование атрибутов для автоматической генерации StaticFactory
#[StaticFactory]
class PaymentMethod
{
    // Автоматически генерируется create()
}

// Или с использованием enum
enum PaymentType: string
{
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
    case CRYPTO = 'crypto';

    public function create(array $config): PaymentMethod
    {
        return match($this) {
            self::CREDIT_CARD => CreditCardPayment::create(
                $config['card_number'],
                $config['expiry_date'],
                $config['cvv']
            ),
            self::PAYPAL => PayPalPayment::create(
                $config['email'],
                $config['password']
            ),
            self::CRYPTO => CryptoPayment::create(
                $config['wallet_address'],
                $config['crypto_type'] ?? 'BTC'
            ),
        };
    }
}

// Pattern matching для создания
$payment = match($type) {
    PaymentType::CREDIT_CARD => PaymentType::create(...),
    PaymentType::PAYPAL => PaymentType::create(...),
    PaymentType::CRYPTO => PaymentType::create(...),
};


************************************** */

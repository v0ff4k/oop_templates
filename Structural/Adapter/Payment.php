<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 30.06.2026 - 21:52
 */
namespace Structural\Adapter;

/**
 * Пример 1: Target Interface - целевой интерфейс
 */
interface PaymentProcessor
{
    public function processPayment(float $amount, string $currency): PaymentResult;
    public function refundPayment(float $amount, string $currency): PaymentResult;
}

/**
 * Adaptee - адаптируемый класс с несовместимым интерфейсом
 */
class PayPalService
{
    public function sendPayment(float $amount, string $currencyCode): array
    {
        // Имитация API PayPal
        return [
            'status' => 'success',
            'transaction_id' => 'PP' . uniqid(),
            'amount' => $amount,
            'currency' => $currencyCode,
            'message' => "Payment of {$amount} {$currencyCode} processed"
        ];
    }

    public function refundTransaction(string $transactionId, float $amount): array
    {
        // Имитация возврата PayPal
        return [
            'status' => 'success',
            'refund_id' => 'REF' . uniqid(),
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'message' => "Refund of {$amount} processed for transaction {$transactionId}"
        ];
    }
}

/**
 * Adapter - адаптер, который делает PayPalService совместимым с PaymentProcessor
 */
class PayPalAdapter implements PaymentProcessor
{
    private PayPalService $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    public function processPayment(float $amount, string $currency): PaymentResult
    {
        $result = $this->payPalService->sendPayment($amount, $currency);

        return new PaymentResult(
            $result['status'] === 'success',
            $result['message'],
            $result['transaction_id'] ?? null,
            $result['amount'],
            $result['currency']
        );
    }

    public function refundPayment(float $amount, string $currency): PaymentResult
    {
        // Для возврата нам нужен transaction_id, который должен храниться где-то
        // В реальном приложении это могло бы быть передано извне
        $transactionId = 'mock_transaction_123'; // Заглушка

        $result = $this->payPalService->refundTransaction($transactionId, $amount);

        return new PaymentResult(
            $result['status'] === 'success',
            $result['message'],
            $result['refund_id'] ?? null,
            $result['amount'],
            $result['currency']
        );
    }
}

/**
 * Another Adaptee - другой адаптируемый класс
 */
class StripeService
{
    public function charge(float $amount, string $currency, string $token): array
    {
        // Имитация API Stripe
        return [
            'id' => 'ch_' . bin2hex(random_bytes(16)),
            'object' => 'charge',
            'amount' => $amount * 100, // Stripe работает в минимальных единицах
            'currency' => strtolower($currency),
            'status' => 'succeeded',
            'captured' => true,
        ];
    }

    public function refund(string $chargeId, float $amount): array
    {
        // Имитация возврата Stripe
        return [
            'id' => 're_' . bin2hex(random_bytes(16)),
            'object' => 'refund',
            'charge' => $chargeId,
            'amount' => $amount * 100,
            'currency' => 'usd',
            'status' => 'succeeded',
        ];
    }
}

/**
 * Adapter for Stripe
 */
class StripeAdapter implements PaymentProcessor
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function processPayment(float $amount, string $currency): PaymentResult
    {
        // Stripe требует токен, который в реальности получают на фронтенде
        $token = 'tok_' . bin2hex(random_bytes(8));

        $result = $this->stripeService->charge($amount, $currency, $token);

        return new PaymentResult(
            $result['status'] === 'succeeded',
            "Payment {$result['id']} {$result['status']}",
            $result['id'],
            $amount,
            $currency
        );
    }

    public function refundPayment(float $amount, string $currency): PaymentResult
    {
        // Здесь нужно хранить charge_id из предыдущей операции
        $chargeId = 'ch_mock_123'; // Заглушка

        $result = $this->stripeService->refund($chargeId, $amount);

        return new PaymentResult(
            $result['status'] === 'succeeded',
            "Refund {$result['id']} {$result['status']}",
            $result['id'],
            $amount,
            $currency
        );
    }
}

/**
 * Result Class
 */
class PaymentResult
{
    public function __construct(
        private bool $success,
        private string $message,
        private ?string $transactionId,
        private float $amount,
        private string $currency
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
}


/**
 * Пример 2: Адаптер для разных логгеров
 */
interface Logger
{
    public function log(string $message, string $level = 'info'): void;
    public function error(string $message): void;
    public function warning(string $message): void;
    public function info(string $message): void;
}

/**
 * Adaptee - старый логгер с другим интерфейсом
 */
class OldLogger
{
    public function write(string $message, int $severity = 0): void
    {
        $levels = [
            0 => 'INFO',
            1 => 'WARNING',
            2 => 'ERROR',
            3 => 'CRITICAL',
        ];

        $level = $levels[$severity] ?? 'INFO';
        $timestamp = date('Y-m-d H:i:s');

        file_put_contents(
            __DIR__ . '/old.log',
            "[$timestamp] [$level] $message\n",
            FILE_APPEND
        );
    }
}

/**
 * Adapter for OldLogger
 */
class OldLoggerAdapter implements Logger
{
    private OldLogger $oldLogger;

    public function __construct(OldLogger $oldLogger)
    {
        $this->oldLogger = $oldLogger;
    }

    public function log(string $message, string $level = 'info'): void
    {
        $severityMap = [
            'info' => 0,
            'warning' => 1,
            'error' => 2,
            'critical' => 3,
        ];

        $severity = $severityMap[$level] ?? 0;
        $this->oldLogger->write($message, $severity);
    }

    public function error(string $message): void
    {
        $this->log($message, 'error');
    }

    public function warning(string $message): void
    {
        $this->log($message, 'warning');
    }

    public function info(string $message): void
    {
        $this->log($message, 'info');
    }
}



/**
 * Пример 3: Адаптер для разных API погоды
 */
interface WeatherService
{
    public function getCurrentTemperature(): float;
    public function getForecast(): array;
}

/**
 * Adaptee - API OpenWeatherMap
 */
class OpenWeatherMapAPI
{
    public function getWeather(string $city): array
    {
        // Имитация API
        return [
            'city' => $city,
            'temp' => rand(-10, 35),
            'humidity' => rand(30, 100),
            'condition' => 'sunny',
            'forecast' => [
                ['day' => 'Mon', 'temp' => rand(-10, 35)],
                ['day' => 'Tue', 'temp' => rand(-10, 35)],
                ['day' => 'Wed', 'temp' => rand(-10, 35)],
            ]
        ];
    }
}

/**
 * Adapter for OpenWeatherMap
 */
class OpenWeatherMapAdapter implements WeatherService
{
    private OpenWeatherMapAPI $api;

    public function __construct(OpenWeatherMapAPI $api)
    {
        $this->api = $api;
    }

    public function getCurrentTemperature(): float
    {
        $data = $this->api->getWeather('Deli');
        return $data['temp'];
    }

    public function getForecast(): array
    {
        $data = $this->api->getWeather('Deli');
        return $data['forecast'];
    }
}

/**
 * Adaptee - API WeatherAPI
 */
class WeatherAPI
{
    public function getCurrent(string $city): array
    {
        return [
            'location' => $city,
            'current' => [
                'temp_c' => rand(-1, 38),
                'condition' => ['text' => 'Sunny'],
            ],
            'forecast' => [
                ['date' => '2026-06-30', 'maxtemp_c' => rand(-1, 38), 'mintemp_c' => rand(-10, 31)],
                ['date' => '2026-07-01', 'maxtemp_c' => rand(-1, 38), 'mintemp_c' => rand(-10, 31)],
                ['date' => '2026-07-02', 'maxtemp_c' => rand(-1, 38), 'mintemp_c' => rand(-10, 31)],
            ]
        ];
    }
}

/**
 * Adapter for WeatherAPI
 */
class WeatherAPIAdapter implements WeatherService
{
    private WeatherAPI $api;

    public function __construct(WeatherAPI $api)
    {
        $this->api = $api;
    }

    public function getCurrentTemperature(): float
    {
        $data = $this->api->getCurrent('Karachi');
        return $data['current']['temp_c'];
    }

    public function getForecast(): array
    {
        $data = $this->api->getCurrent('Karachi');
        return array_map(function ($day) {
            return [
                'day' => date('D', strtotime($day['date'])),
                'temp' => $day['maxtemp_c']
            ];
        }, $data['forecast']);
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Target Interface - определяет интерфейс, который ожидается клиентами
 *    Adaptee - существующий класс с несовместимым интерфейсом
 *    Adapter - класс, который реализует Target и содержит экземпляр Adaptee
 *    Client - использует Target интерфейс, не зная о существовании Adaptee
 *
 * Преимущества:
 *    Совместимость - позволяет использовать несовместимые классы вместе
 *    Переиспользование - старый код можно адаптировать под новые интерфейсы
 *    Гибкость - легко добавлять новые адаптеры для разных сервисов
 *    Разделение ответственности - адаптер отделяет клиентов от реализации
 * Недостатки:
 *    Сложность - добавляет дополнительный уровень косвенности
 *    Производительность - может влиять на производительность
 *    Обертка - может скрывать истинную природу адаптируемого класса
 *    Избыточность - если интерфейсы уже совместимы
 *
 * Где используется в фреймворках:
 * 1. Laravel's Cache Adapters
 *    use Illuminate\Cache\RedisStore;
 *    use Illuminate\Cache\MemcachedStore;
 *    use Illuminate\Cache\DatabaseStore;
 *    // Разные драйверы кэша через общий интерфейс
 *    Cache::store('redis'); // RedisAdapter
 *    Cache::store('memcached'); // MemcachedAdapter
 *    Cache::store('database'); // DatabaseAdapter
 * 2. Laravel's Queue Adapters
 *    use Illuminate\Queue\RedisQueue;
 *    use Illuminate\Queue\DatabaseQueue;
 *    use Illuminate\Queue\SyncQueue;
 *    // Разные брокеры очередей
 *    Queue::connection('redis'); // RedisAdapter
 *    Queue::connection('database'); // DatabaseAdapter
 *    Queue::connection('sync'); // SyncAdapter
 * 3. Laravel's Mail Adapters
 *    use Swift_Mailer;
 *    use Illuminate\Mail\MailManager;
 *    // Разные транспорты для отправки почты
 *    Mail::send([], [], function ($message) {
 *        $message->to('example@example.com');
 *    });
 *    // Использует Swift_Mailer как адаптер для разных SMTP/SES/etc
 * 4. Laravel's Notification Channels
 *    use Illuminate\Notifications\Channels\DatabaseChannel;
 *    use Illuminate\Notifications\Channels\MailChannel;
 *    use Illuminate\Notifications\Channels\NexmoChannel;
 *    // Разные каналы уведомлений
 *    $notifiable->notify(new InvoicePaid($invoice));
 *    // Автоматически выбирает нужный канал (DatabaseAdapter, MailAdapter, NexmoAdapter)
 * 5. Laravel's Eloquent Relations
 *    class User extends Model
 *    {
 *       public function posts()
 *    {
 *            return $this->hasMany(Post::class);
 *        }
 *        public function profile()
 *        {
 *            return $this->hasOne(Profile::class);
 *        }
 *    }
 *    // HasMany, HasOne, BelongsTo - это адаптеры для разных типов отношений
 * 6. Laravel's HTTP Client
 *    use Illuminate\Support\Facades\Http;
 *    // Адаптер для разных HTTP клиентов (Guzzle, CURL, etc)
 *    $response = Http::get('https://api.example.com/data');
 * 7. Symfony's HttpClient
 *    use Symfony\Component\HttpClient\HttpClient;
 *    use Symfony\Component\HttpClient\Psr18Client;
 *    // Адаптер для PSR-18 интерфейса
 *    $client = HttpClient::create();
 *    $request = new Psr18Client($client);
 * 8. Laravel's Payment Gateways
 *    interface PaymentGateway
 *    {
 *        public function charge(array $params): PaymentResult;
 *        public function refund(string $transactionId, float $amount): PaymentResult;
 *    }
 *    class StripeGateway implements PaymentGateway
 *    {
 *        // Адаптер для Stripe API
 *    }
 *    class PayPalGateway implements PaymentGateway
 *    {
 *        // Адаптер для PayPal API
 *    }
 *    class MollieGateway implements PaymentGateway
 *    {
 *        // Адаптер для Mollie API
 *    }
 * 9. Laravel's Socialite Providers
 *    // Адаптеры для разных OAuth провайдеров
 *    return Socialite::driver('github')->redirect();
 *    return Socialite::driver('facebook')->user();
 *    return Socialite::driver('google')->user();
 * 10. Laravel's Filesystem Adapters
 *    Storage::disk('s3'); // Адаптер для AWS S3
 *    Storage::disk('local'); // Адаптер для локального хранилища
 *    Storage::disk('rackspace'); // Адаптер для Rackspace
 *
 * Когда полезен:
 *    Интеграция legacy кода - адаптация старого кода под новые интерфейсы
 *    Использование сторонних библиотек - унификация разных API
 *    Миграция - постепенный переход с одной системы на другую
 *    Тестирование - создание моков/заглушек через адаптеры
 *    Мультиязычность - адаптация разных форматов данных
 */


echo "=== Payment Adapter Example ===\n";
// Создаем конкретные сервисы
$payPalService = new PayPalService();
$stripeService = new StripeService();

// Создаем адаптеры
$payPalAdapter = new PayPalAdapter($payPalService);
$stripeAdapter = new StripeAdapter($stripeService);

// Используем через общий интерфейс
$processors = [$payPalAdapter, $stripeAdapter];

foreach ($processors as $i => $processor) {
    $result = $processor->processPayment(100, 'USD');
    echo 'Processor #' . ($i+1) . ': ' . ($result->isSuccess() ? 'SUCCESS' : 'FAILED')
        . ' - ' . $result->getMessage() . "\n";
}
//Processor #1: SUCCESS - Payment of 100 USD processed
//Processor #2: SUCCESS - Payment ch_71cd58646cca04bd341afa3a3c1a68d5 succeeded

echo "\n=== Logger Adapter Example ===\n";
// Используем старый логгер через адаптер
$oldLogger = new OldLogger();
$logger = new OldLoggerAdapter($oldLogger);

$logger->info('Application started');
$logger->warning('Low disk space');
$logger->error('Database connection failed');
// some  file manipulations.
echo "Logs written to old.log\n";


echo "\n=== Weather Service Adapter Example ===\n";
$openWeather = new OpenWeatherMapAPI();
$weatherAPI = new WeatherAPI();

$openWeatherAdapter = new OpenWeatherMapAdapter($openWeather);
$weatherAPIAdapter = new WeatherAPIAdapter($weatherAPI);

$weatherServices = [$openWeatherAdapter, $weatherAPIAdapter];

foreach ($weatherServices as $i => $service) {
    $temp = $service->getCurrentTemperature();
    $forecast = $service->getForecast();

    echo 'Weather Service #' . ($i+1) . ": Current temp: {$temp}°C\n";
    echo "Forecast:\n";
    foreach ($forecast as $day) {
        echo " - {$day['day']}: {$day['temp']}°C\n";
    }
}
//Weather Service #1: Current temp: -4°C
//Forecast:
// - Mon: 16°C
//- Tue: 22°C
//- Wed: 23°C
//Weather Service #2: Current temp: 21°C
//Forecast:
// - Tue: 27°C
//- Wed: 10°C
//- Thu: 16°C

<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 17.08.2026 - 13:57
 */

namespace Creational\Prototype;

use Exception;

/**
 * Prototype - интерфейс для клонируемых объектов
 */
interface BasicPrototype
{
    public function clone(): BasicPrototype;

    public function getName(): string;

    public function setName(string $name): void;

    public function getData(): array;

    public function setData(array $data): void;
}

/**
 * Concrete Prototype 1 - Простой прототип
 */
class SimplePrototype implements BasicPrototype
{
    private string $name;
    private array $data = [];

    public function __construct(string $name = '', array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function __clone()
    {
        // Глубокое клонирование для сложных объектов
        $this->data = array_map(
            function ($item) {
                return is_object($item) ? clone $item : $item;
            },
            $this->data
        );
    }
}

/**
 * Concrete Prototype 2 - Сложный прототип с вложенными объектами
 */
class ComplexPrototype implements BasicPrototype
{
    private string $name;
    private array $config = [];
    private array $metadata = [];
    private array $children = [];

    public function __construct(string $name = '', array $config = [], array $metadata = [])
    {
        $this->name = $name;
        $this->config = $config;
        $this->metadata = $metadata;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function addChild(BasicPrototype $child): void
    {
        $this->children[] = $child;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getData(): array
    {
        return [
            'name' => $this->name,
            'config' => $this->config,
            'metadata' => $this->metadata,
            'children' => array_map(fn ($child) => $child->getData(), $this->children),
        ];
    }

    public function setData(array $data): void
    {
        $this->name = $data['name'] ?? '';
        $this->config = $data['config'] ?? [];
        $this->metadata = $data['metadata'] ?? [];

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $child = new self();
                $child->setData($childData);
                $this->children[] = $child;
            }
        }
    }

    public function __clone()
    {
        // Глубокое клонирование вложенных объектов
        $this->config = array_map(function ($item) {
            return is_object($item) ? clone $item : $item;
        }, $this->config);

        $this->metadata = array_map(function ($item) {
            return is_object($item) ? clone $item : $item;
        }, $this->metadata);

        $clonedChildren = [];
        foreach ($this->children as $child) {
            $clonedChildren[] = clone $child;
        }
        $this->children = $clonedChildren;
    }
}

/**
 * Пример 2: Prototype для конфигураций
 */
class ConfigPrototype implements BasicPrototype
{
    private string $environment;
    private array $settings = [];
    private array $services = [];

    public function __construct(string $environment = 'production')
    {
        $this->environment = $environment;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->environment;
    }

    public function setName(string $name): void
    {
        $this->environment = $name;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function addService(string $name, array $config): void
    {
        $this->services[$name] = $config;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function getData(): array
    {
        return [
            'environment' => $this->environment,
            'settings' => $this->settings,
            'services' => $this->services,
        ];
    }

    public function setData(array $data): void
    {
        $this->environment = $data['environment'] ?? 'production';
        $this->settings = $data['settings'] ?? [];
        $this->services = $data['services'] ?? [];
    }

    public function __clone()
    {
        // Клонирование сервисов
        $clonedServices = [];
        foreach ($this->services as $name => $config) {
            $clonedServices[$name] = $config; // Обычно конфигурации не клонируются
        }
        $this->services = $clonedServices;
    }
}

/**
 * Пример 3: Prototype для форм
 */
class FormPrototype implements BasicPrototype
{
    private string $name;
    private array $fields = [];
    private array $validations = [];
    private array $attributes = [];

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addField(string $name, string $type, array $options = []): void
    {
        $this->fields[$name] = [
            'type' => $type,
            'options' => $options,
        ];
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function addValidation(string $field, string $rule, mixed $value = null): void
    {
        $this->validations[$field][$rule] = $value;
    }

    public function getValidations(): array
    {
        return $this->validations;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getData(): array
    {
        return [
            'name' => $this->name,
            'fields' => $this->fields,
            'validations' => $this->validations,
            'attributes' => $this->attributes,
        ];
    }

    public function setData(array $data): void
    {
        $this->name = $data['name'] ?? '';
        $this->fields = $data['fields'] ?? [];
        $this->validations = $data['validations'] ?? [];
        $this->attributes = $data['attributes'] ?? [];
    }

    public function __clone()
    {
        // Клонирование полей и валидаций
        $this->fields = array_map(function ($field) {
            return $field; // Обычно конфигурация полей не клонируется
        }, $this->fields);

        $this->validations = array_map(function ($rules) {
            return $rules; // Правила валидации не клонируются
        }, $this->validations);
    }
}

/**
 * Пример 4: Prototype для уведомлений
 */
class NotificationPrototype implements BasicPrototype
{
    private string $channel;
    private array $recipients = [];
    private string $message = '';
    private array $attachments = [];

    public function __construct(string $channel = 'email')
    {
        $this->channel = $channel;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->channel;
    }

    public function setName(string $name): void
    {
        $this->channel = $name;
    }

    public function addRecipient(string $email, string $name = ''): void
    {
        $this->recipients[] = [
            'email' => $email,
            'name' => $name,
        ];
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function addAttachment(string $path, string $type = 'application/octet-stream'): void
    {
        $this->attachments[] = [
            'path' => $path,
            'type' => $type,
            'filename' => basename($path),
        ];
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getData(): array
    {
        return [
            'channel' => $this->channel,
            'recipients' => $this->recipients,
            'message' => $this->message,
            'attachments' => $this->attachments,
        ];
    }

    public function setData(array $data): void
    {
        $this->channel = $data['channel'] ?? 'email';
        $this->recipients = $data['recipients'] ?? [];
        $this->message = $data['message'] ?? '';
        $this->attachments = $data['attachments'] ?? [];
    }

    public function __clone()
    {
        // Клонирование вложений
        $clonedAttachments = [];
        foreach ($this->attachments as $attachment) {
            $clonedAttachments[] = $attachment; // Обычно файлы не клонируются
        }
        $this->attachments = $clonedAttachments;
    }
}

/**
 * Пример 5: Prototype для отчетов
 */
class ReportPrototype implements BasicPrototype
{
    private string $type;
    private array $data = [];
    private array $filters = [];
    private string $format = 'pdf';

    public function __construct(string $type = 'sales')
    {
        $this->type = $type;
    }

    public function clone(): BasicPrototype
    {
        return clone $this;
    }

    public function getName(): string
    {
        return $this->type;
    }

    public function setName(string $name): void
    {
        $this->type = $name;
    }

    public function addData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getData(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'filters' => $this->filters,
            'format' => $this->format,
        ];
    }

    public function setData(array $data): void
    {
        $this->type = $data['type'] ?? 'sales';
        $this->data = $data['data'] ?? [];
        $this->filters = $data['filters'] ?? [];
        $this->format = $data['format'] ?? 'pdf';
    }

    public function addFilter(string $field, mixed $value): void
    {
        $this->filters[$field] = $value;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function __clone()
    {
        // Глубокое клонирование данных
        $this->data = array_map(function ($value) {
            return is_object($value) ? clone $value : $value;
        }, $this->data);
    }
}


/**
 * Пример использования
 *
 * Основная идея
 *    Prototype — интерфейс для клонирования объектов
 *    ConcretePrototype — конкретный объект, который можно клонировать
 *    Client — использует прототипы для создания новых объектов
 *
 * Как это работает:
 *    Prototype определяет метод clone() для создания копий объектов
 *    ConcretePrototype реализует метод clone() и может переопределять __clone() для глубокого копирования
 *    Client создает новые объекты, клонируя существующие прототипы
 *
 * Преимущества:
 *    Производительность — клонирование может быть быстрее создания с нуля
 *    Гибкость — можно создавать объекты без знания их конкретных классов
 *    Уменьшение наследования — избегаем сложных иерархий наследования
 *    Удобство для сложных объектов — легко создавать объекты с предустановленными состояниями
 *    Поддержка неполного состояния — можно клонировать только часть состояния
 * Недостатки:
 *    Сложность клонирования — глубокое копирование может быть сложным
 *    Проблемы с ресурсами — клонирование объектов с ресурсами (например, соединения с БД)
 *    Поддержка — нужно поддерживать все прототипы в актуальном состоянии
 *    Циклические ссылки — могут вызывать бесконечные рекурсии при клонировании
 *    Чистота копии — нужно решать, делать мелкое или глубокое копирование
 *
 * Где используется в фреймворках:
 * 1. Laravel's Eloquent Models
 *    // Laravel Eloquent использует Prototype для клонирования моделей
 *    $user = User::find(1);
 *    $clone = $user->replicate(); // Клонирование модели
 *    $clone->id = null; // Сброс ID для нового сохранения
 *    $clone->save();
 *    // Или через toArray и new
 *    $data = $user->toArray();
 *    $clone = new User($data);
 *    $clone->id = null;
 *    $clone->save();
 * 2. Symfony's Form Types
 *    // Symfony Form Component использует Prototype для форм
 *    $formType = new UserType();
 *    $cloneType = clone $formType; // Клонирование типа формы
 *    // Или через buildForm
 *    $form = $this->createFormBuilder($data)
 *        ->add('name', TextType::class)
 *        ->add('email', EmailType::class)
 *        ->getForm();
 *    $cloneForm = clone $form; // Клонирование формы
 * 3. Yii Framework's Component
 *    // Yii Framework использует Prototype для компонентов
 *    $component = new \yii\base\Component();
 *    $component->property = 'value';
 *    $clone = clone $component; // Клонирование компонента
 *    $clone->property = 'modified';
 *    // Или с событиями
 *    $component->on('event', function () {  // ...
 *        });
 *    $clone = clone $component; // События тоже клонируются
 * 4. Laravel's Notification Channels
 *    // Laravel Notifications используют Prototype для каналов
 *    $notification = new OrderShipped($order);
 *    // Клонирование уведомления для разных каналов
 *    $emailNotification = clone $notification;
 *    $emailNotification->toMail($notifiable);
 *    $databaseNotification = clone $notification;
 *    $databaseNotification->toDatabase($notifiable);
 *    // Или с via методом
 *    $via = $notification->via($notifiable); // ['mail', 'database']
 * 5. Laravel's Mailable
 *    // Laravel Mailables используют Prototype
 *    $mail = new OrderShipped($order);
 *    $mail->subject('Your order has been shipped!');
 *    $mail->with(['order' => $order]);
 *    $cloneMail = clone $mail; // Клонирование мейла
 *    $cloneMail->subject('New subject');
 *    $cloneMail->with(['order' => $modifiedOrder]);
 * 6. Symfony's Event
 *    // Symfony Events используют Prototype
 *    $event = new FilterResponseEvent($request, $response);
 *    $cloneEvent = clone $event; // Клонирование события
 *    // Или с GenericEvent
 *    $event = new GenericEvent($subject, ['name' => 'value']);
 *    $clone = clone $event;
 * 7. Laravel's Request
 *    // Laravel Request использует Prototype
 *    $request = Request::capture();
 *    $clone = clone $request; // Клонирование запроса
 *    // Или с Illuminate\Http\Request
 *    $request = new Request($query, $request, $attributes, $cookies, $files, $server, $content);
 *    $clone = clone $request;
 * 8. Doctrine's Entity
 *    // Doctrine Entities используют Prototype
 *    $entity = $entityManager->find('User', 1);
 *    $clone = clone $entity; // Клонирование сущности
 *    // Но нужно сбросить идентификатор
 *    $clone->setId(null);
 *    $entityManager->persist($clone);
 *    $entityManager->flush();
 * 9. Laravel's Collection
 *    // Laravel Collections используют Prototype
 *    $collection = collect([1, 2, 3]);
 *    $clone = clone $collection; // Клонирование коллекции
 *    // Или с map
 *    $newCollection = $collection->map(fn($item) => $item * 2);
 * 10. Symfony's OptionsResolver
 *    // Symfony OptionsResolver использует Prototype
 *    $resolver = new OptionsResolver();
 *    $resolver->setDefaults(['debug' => true]);
 *    $clone = clone $resolver; // Клонирование резолвера
 *    $clone->setDefaults(['debug' => false]);
 *
 * Когда полезен:
 *    Когда создание объектов дорого — клонирование быстрее создания с нуля
 *    Когда нужно сохранить состояние — клонирование сохраняет текущее состояние
 *    Когда нужно создать похожие объекты — небольшие изменения от прототипа
 *    Когда нужно избежать наследования — вместо создания иерархий классов
 *
 *
 *        Разница между Prototype и другими паттернами:
 * Prototype = Клонирование существующего
 *   Через clone()
 *     Сохраняет состояние
 *       Быстрое создание
 * Builder = Пошаговое построение
 *   Через build()
 *     Может сбрасывать состояние
 *       Контролируемое создание
 * Factory Method = Создание одного объекта
 *   Через фабричный метод
 *     Создает новый объект
 *       Гибкое создание
 * Abstract Factory = Создание семейства объектов
 *   Через фабричные методы
 *     Создает новые объекты
 *       Совместимое создание
 *
 */

try {
    echo "=== Prototype Pattern Example ===\n\n";


    // Пример 1: Simple Prototype
    echo "=== Simple Prototype ===\n";

    $data1 = ['key1' => 'value1', 'key2' => 'value2'];
    $data2 = ['key1' => 'modified1', 'key3' => 'value3'];

    $prototype1 = new SimplePrototype('original', $data1);
    $clone1 = $prototype1->clone();
    $clone1->setName('clone1');
    $clone1->setData($data2);

    echo 'Original: ' . json_encode($prototype1->getData()) . "\n";
    echo 'Clone: ' . json_encode($clone1->getData()) . "\n\n";


    // Пример 2: Complex Prototype
    echo "=== Complex Prototype ===\n";

    $child1 = new ComplexPrototype('child1', ['child_config' => true]);
    $child2 = new ComplexPrototype('child2', ['child_config' => false]);

    $prototype2 = new ComplexPrototype('parent', ['parent_config' => true], ['meta' => 'parent']);
    $prototype2->addChild($child1);
    $prototype2->addChild($child2);

    $clone2 = $prototype2->clone();
    $clone2->setName('cloned_parent');
    $clone2->setConfig(['parent_config' => false]);

    echo 'Original children: ' . json_encode(array_map(fn ($c) => $c->getName(), $prototype2->getChildren())) . "\n";
    echo 'Clone children: ' . json_encode(array_map(fn ($c) => $c->getName(), $clone2->getChildren())) . "\n";
    echo 'Original data: ' . json_encode($prototype2->getData()) . "\n";
    echo 'Clone data: ' . json_encode($clone2->getData()) . "\n\n";


    // Пример 3: Config Prototype
    echo "=== Config Prototype ===\n";

    $devConfig = new ConfigPrototype('development');
    $devConfig->setSettings(['debug' => true, 'cache' => false]);
    $devConfig->addService('database', ['host' => 'localhost', 'debug' => true]);
    $devConfig->addService('cache', ['driver' => 'file']);

    $prodConfig = $devConfig->clone();
    $prodConfig->setName('production');
    $prodConfig->setSettings(['debug' => false, 'cache' => true]);
    $prodConfig->addService('database', ['host' => 'prod-db', 'debug' => false]);

    echo 'Dev config: ' . json_encode($devConfig->getData()) . "\n";
    echo 'Prod config: ' . json_encode($prodConfig->getData()) . "\n\n";


    // Пример 4: Form Prototype
    echo "=== Form Prototype ===\n";

    $loginForm = new FormPrototype('login');
    $loginForm->addField('email', 'email');
    $loginForm->addField('password', 'password');
    $loginForm->addValidation('email', 'required');
    $loginForm->addValidation('password', 'minLength', 6);
    $loginForm->setAttribute('class', 'login-form');

    $registerForm = $loginForm->clone();
    $registerForm->setName('register');
    $registerForm->addField('username', 'text');
    $registerForm->addValidation('username', 'required');
    $registerForm->setAttribute('class', 'register-form');

    echo 'Login form fields: ' . json_encode($loginForm->getFields()) . "\n";
    echo 'Register form fields: ' . json_encode($registerForm->getFields()) . "\n\n";


    // Пример 5: Notification Prototype
    echo "=== Notification Prototype ===\n";

    $emailNotification = new NotificationPrototype('email');
    $emailNotification->addRecipient('user@example.com', 'Vasily Pupkin');
    $emailNotification->setMessage('Welcome to our service!');

    $smsNotification = $emailNotification->clone();
    $smsNotification->setName('sms');
    $smsNotification->addRecipient('+1234567890');
    $smsNotification->setMessage('Your verification code is 123456');

    echo 'Email notification: ' . json_encode($emailNotification->getData()) . "\n";
    echo 'SMS notification: ' . json_encode($smsNotification->getData()) . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}




/* Для PHP 8.4 **************************************

// Использование атрибутов для автоматической генерации Prototype
#[Prototype]
class ConfigPrototype
{
    private string $environment;
    private array $settings = [];

    public function __construct(string $environment = 'production')
    {
        $this->environment = $environment;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}

// Генерация Prototype через атрибуты(+Reflection)
class PrototypeBuilder
{
    public function build(string $class): object
    {
        $reflector = new ReflectionClass($class);
        $instance = $reflector->newInstance();

        // Автоматическое создание метода clone
        if (!$reflector->hasMethod('clone')) {
            $method = new ReflectionMethod($class, '__clone');
            if (!$method->isPublic()) {
                // Генерация публичного метода clone
                $prototype = new class($class, $instance) {
                    private string $class;
                    private object $instance;

                    public function __construct(string $class, object $instance)
                    {
                        $this->class = $class;
                        $this->instance = $instance;
                    }

                    public function clone(): object
                    {
                        return clone $this->instance;
                    }
                };

                return $prototype;
            }
        }

        return $instance;
    }
}

// Pattern matching для автоматического создания Prototype
public function getPrototype(string $type, array $config = []): object
{
    return match ($type) {
        'config' => (new ConfigPrototype($config['environment'] ?? 'production'))->clone(),
        'form' => (new FormPrototype($config['name'] ?? ''))->clone(),
        'notification' => (new NotificationPrototype($config['channel'] ?? 'email'))->clone(),
        'report' => (new ReportPrototype($config['type'] ?? 'sales'))->clone(),
        'simple' => (new SimplePrototype($config['name'] ?? '', $config['data'] ?? []))->clone(),
        'complex' => (new ComplexPrototype(
            $config['name'] ?? '',
            $config['config'] ?? [],
            $config['metadata'] ?? []
        ))->clone(),
        default => throw new InvalidArgumentException("Unknown prototype type"),
    };
}

// Enum для типов Prototype
enum PrototypeType: string
{
    case CONFIG = 'config';
    case FORM = 'form';
    case NOTIFICATION = 'notification';
    case REPORT = 'report';
    case SIMPLE = 'simple';
    case COMPLEX = 'complex';
    case MODEL = 'model';
    case ENTITY = 'entity';
    case REQUEST = 'request';
    case RESPONSE = 'response';
    case EVENT = 'event';
    case NOTIFICATION = 'notification';
    case MAILLABLE = 'mailable';
    case FORM_TYPE = 'form_type';
}

class PrototypeFactory
{
    public function create(PrototypeType $type, array $config = []): object
    {
        return match ($type) {
            PrototypeType::CONFIG => (new ConfigPrototype(
                $config['environment'] ?? 'production'
            ))->clone(),
            PrototypeType::FORM => (new FormPrototype(
                $config['name'] ?? ''
            ))->clone(),
            PrototypeType::NOTIFICATION => (new NotificationPrototype(
                $config['channel'] ?? 'email'
            ))->clone(),
            PrototypeType::REPORT => (new ReportPrototype(
                $config['type'] ?? 'sales'
            ))->clone(),
            PrototypeType::SIMPLE => (new SimplePrototype(
                $config['name'] ?? '',
                $config['data'] ?? []
            ))->clone(),
            PrototypeType::COMPLEX => (new ComplexPrototype(
                $config['name'] ?? '',
                $config['config'] ?? [],
                $config['metadata'] ?? []
            ))->clone(),
            PrototypeType::MODEL => (new ModelPrototype())->clone(),
            PrototypeType::ENTITY => (new EntityPrototype())->clone(),
            PrototypeType::REQUEST => (new RequestPrototype())->clone(),
            PrototypeType::RESPONSE => (new ResponsePrototype())->clone(),
            PrototypeType::EVENT => (new EventPrototype())->clone(),
            PrototypeType::MAILLABLE => (new MailablePrototype())->clone(),
            PrototypeType::FORM_TYPE => (new FormTypePrototype())->clone(),
        };
    }
}

***************************************** */

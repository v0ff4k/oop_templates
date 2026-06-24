<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 15.06.2026 - 14:22
 */

namespace Behavioral\Mediator;

/**
 * Интерфейс Посредника
 */
interface Mediator
{
    public function notify(object $sender, string $event, array $data = []): void;
}

interface UIMediator extends Mediator
{
    public function buttonClick(string $buttonName): void;

    public function textChanged(string $textBoxName, string $text): void;
}

/**
 * Class ChatRoom
 * Конкретный Посредник - Комната чата
 *
 * @package Behavioral\Mediator
 */
class ChatRoom implements Mediator
{
    /** @var User[] */
    private array $users = [];

    public function __construct(
        private string $name
    ) {
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $this->users[$user->getName()] = $user;
        $user->setMediator($this);
    }

    /**
     * @param object $sender
     * @param string $event
     * @param array $data
     */
    public function notify(object $sender, string $event, array $data = []): void
    {
        match ($event) {
            'message' => $this->broadcastMessage($sender, $data['text'] ?? ''),
            'join' => $this->notifyJoin($sender),
            'leave' => $this->notifyLeave($sender),
            default => null
        };
    }

    /**
     * @param User $sender
     * @param string $text
     */
    private function broadcastMessage(User $sender, string $text): void
    {
        foreach ($this->users as $user) {
            if ($user !== $sender) {
                $user->receiveMessage($sender, $text);
            }
        }
    }

    /**
     * @param User $user
     */
    private function notifyJoin(User $user): void
    {
        echo "User {$user->getName()} IS joined the chat\n";
    }

    /**
     * @param User $user
     */
    private function notifyLeave(User $user): void
    {
        echo "User {$user->getName()} HAS left the chat\n";
    }
}

/**
 * Class User
 * Базовый компонент (Colleague)
 *
 * @package Behavioral\Mediator
 */
abstract class User
{
    public function __construct(
        protected string   $name,
        protected Mediator $mediator
    ) {
        $this->mediator->notify($this, 'join');
    }

    abstract public function receiveMessage(User $sender, string $text): void;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Mediator $mediator
     */
    public function setMediator(Mediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    /**
     * @param string $text
     */
    public function sendMessage(string $text): void
    {
        $this->mediator->notify($this, 'message', ['text' => $text]);
    }

}

/**
 * Class GuestUser
 * Конкретный компонент
 *
 * @package Behavioral\Mediator
 */
class GuestUser extends User
{
    /**
     * @param User $sender
     * @param string $text
     */
    public function receiveMessage(User $sender, string $text): void
    {
        echo "[{$this->name}] Received from {$sender->getName()}: {$text}\n";
    }
}

/**
 * Class AdminUser
 * Другой конкретный компонент
 *
 * @package Behavioral\Mediator
 */
class AdminUser extends User
{
    /**
     * @param User $sender
     * @param string $text
     */
    public function receiveMessage(User $sender, string $text): void
    {
        echo "[ADMIN {$this->name}] ALERT from {$sender->getName()}: {$text}\n";
    }
}

/**
 * Class Button
 * Пример 2: UI Компоненты
 *
 * @package Behavioral\Mediator
 */
class Button
{
    public function __construct(
        private string     $name,
        private UIMediator $mediator
    ) {
    }

    public function click(): void
    {
        $this->mediator->notify($this, 'button_click', ['button' => $this->name]);
    }
}

/**
 * Class TextBox
 * @package Behavioral\Mediator
 */
class TextBox
{
    private ?string $text = null;

    public function __construct(
        private string     $name,
        private UIMediator $mediator
    ) {
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
        $this->mediator->notify($this, 'text_changed', ['text' => $text]);
    }
}

/**
 * Class ConcreteUIMediator
 * Реализация медиатора
 *
 * @package Behavioral\Mediator
 */
class ConcreteUIMediator implements UIMediator
{
    private ?Button $submitButton = null;
    private ?TextBox $inputField = null;

    /**
     * @param Button $button
     */
    public function registerButton(Button $button): void
    {
        $this->submitButton = $button;
    }

    /**
     * @param TextBox $textBox
     */
    public function registerTextBox(TextBox $textBox): void
    {
        $this->inputField = $textBox;
    }

    /**
     * @param object $sender
     * @param string $event
     * @param array $data
     */
    public function notify(object $sender, string $event, array $data = []): void
    {
        match (true) {
            $sender instanceof Button && $event === 'button_click'
                    => $this->buttonClick($data['button']),
            $sender instanceof TextBox && $event === 'text_changed'
                    => $this->textChanged($data['textBox'] ?? '', $data['text'] ?? ''),
            default => null
        };
    }

    /**
     * @param string $buttonName
     */
    public function buttonClick(string $buttonName): void
    {
        if ($this->inputField?->getText()) {
            echo "Processing: {$this->inputField->getText()}\n";
        }
    }

    /**
     * @param string $textBoxName
     * @param string $text
     */
    public function textChanged(string $textBoxName, string $text): void
    {
        if (strlen($text) > 5) {
            echo "Warning: Text too long!\n";
        }
    }
}

// Использование атрибутов для автоматической регистрации
/**
 * Class SendWelcomeEmail
 * Еще пример реализации медиатора, на отправке письма при регистрации.
 *
 * @package Behavioral\Mediator
 */
#[Listener(event: 'user.register')]
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // ...
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Mediator определяет интерфейс для взаимодействия
 *    Colleague (User, Button) - компоненты, которые общаются через посредника
 *    ConcreteMediator (ChatRoom, ConcreteUIMediator) - координирует компоненты
 *    Компоненты знают только о посреднике, а не друг о друге(реализация слабой связанностью)
 *
 * Преимущества:
 *    Уменьшение связанности - компоненты не зависят друг от друга
 *    Централизованное управление - логика взаимодействия в одном месте
 *    Проще тестировать - можно тестировать компоненты отдельно(+)
 *    Гибкость - можно менять поведение, меняя посредника
 *
 * Реальные примеры из фреймворков:
 *    1. Laravel Эvent Service ProvidэрЪ
 *    // events.php
 *    $listen = [
 *      'App\Events\OrderShipped' => [
 *        'App\Listeners\SendShipmentNotification',
 *      ],
 *    ...
 *    ];
 *    // Когда событие происходит, Laravel через поср-а вызывает все(задеклар-е/имею-ся) слушатели
 *    event(new OrderShipped($order));
 * 2. Symfony EventDispatcher
 *    $dispatcher = new EventDispatcher();
 *    $dispatcher->addListener('user.register', function(UserEvent $event) {
 *      // Логика после регистрации
 *    });
 *    // Посредник dispatch уведомляет всех слушателей
 *    $dispatcher->dispatch(new UserEvent($user), 'user.register');
 * 3. Yii2 Event Handling
 *    class User extends Component
 *    {
 *      const EVENT_LOGIN = 'login';
 *
 *      public function login()
 *      {
 *        // ... логика аутентификации
 *        $this->trigger(self::EVENT_LOGIN);
 *      }
 *    }
 *    // Посредник (компонент) уведомляет слушателей
 *    $observer = new class {
 *      public function onLogin(Event $event) {
 *        // Сохранение в лог
 *      }
 *    };
 *    $user->on(User::EVENT_LOGIN, [$observer, 'onLogin']);
 * 4. Doctrine Event Manager
 *    $eventManager = new EventManager();
 *    $eventManager->addEventListener(['preUpdate'], new MyListener());
 *
 *    // Посредник уведомляет слушателей перед обновлением сущности
 *    $eventManager->dispatchEvent('preUpdate', $eventArgs);
 * 5. WordPress Hooks
 *    // add_action - регистрация слушателя
 *    add_action('user_register', function($user_id) {
 *        // Отправка приветственного email
 *    });
 *    // do_action - уведомление посредником
 *    do_action('user_register', $user_id);
 *
 * Когда полезен:
 *    UI Frameworks - диалоги между кнопками, текстовыми полями, окнами
 *    Чаты и мессенджеры - комнаты чата, групповые обсуждения
 *    Системы событий - обработка бизнес-процессов
 *    Многопоточность - координация между потоками
 *    Игры - взаимодействие между игровыми объектами
 */

echo "=== Chat Room Example ===\n";
$chat = new ChatRoom('General');
$guest = new GuestUser('Alice', $chat);
$admin = new AdminUser('Bob', $chat);

$chat->addUser($guest);
$chat->addUser($admin);

$guest->sendMessage('Hello everyone!');
$admin->sendMessage('Welcome!');

echo "\n=== UI Components Example ===\n";
$uiMediator = new ConcreteUIMediator();

$button = new Button('submitBtn', $uiMediator);
$input = new TextBox('username', $uiMediator);
// do the Mediator stuff.
$uiMediator->registerButton($button);
$uiMediator->registerTextBox($input);

$input->setText('john_doe');
$button->click();

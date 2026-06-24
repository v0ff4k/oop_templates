<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 15.06.2026 - 21:27
 */

namespace Behavioral\Observer;

/**
 * Interface Subject
 * Subject - субъект, за которым наблюдадают
 *
 * @package Behavioral\Observer
 */
interface Subject
{
    public function attach(Observer $observer): void;

    public function detach(Observer $observer): void;

    public function notify(): void;
}

/**
 * Interface Observer
 * Observer - наблюдатель
 *
 * @package Behavioral\Observer
 */
interface Observer
{
    public function update(Subject $subject): void;
}

/**
 * Class BlogPost
 * Пример 1: Конкретный Subject - Блог пост
 *
 * @package Behavioral\Observer
 */
class BlogPost implements Subject
{
    /** @var Observer[] */
    private array $observers = [];
    private string $title;
    private string $content;
    private bool $published = false;

    /**
     * Boilerplate, так как не все указано, оставлено для читаемости.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @param Observer $observer
     */
    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;

        echo 'Observer attached: ' . get_class($observer) . "\n";
    }

    /**
     * @param Observer $observer
     */
    public function detach(Observer $observer): void
    {
        $this->observers = array_filter(
            $this->observers,
            fn ($obs) => $obs !== $observer
        );

        echo 'Observer detached: ' . get_class($observer) . "\n";
    }

    public function publish(): void
    {
        $this->published = true;
        $this->notify();
    }

    /**
     * Уведомление.
     */
    public function notify(): void
    {
        echo "BlogPost changed! Notifying observers...\n";

        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->notify();
    }

    public function isPublished(): bool
    {
        return $this->published;
    }
}

/**
 * Class EmailNotification
 * Конкретный Observer - Уведомление подписчиков
 *
 * @package Behavioral\Observer
 */
class EmailNotification implements Observer
{
    /**
     * @param Subject $subject
     */
    public function update(Subject $subject): void
    {
        if ($subject instanceof BlogPost && $subject->isPublished()) {
            // do something, EmailNotification update-able, instead current stub.

            echo "[Email] Sending notification: '{$subject->getTitle()}' is now published!\n";
        }
    }
}

/**
 * Class SitemapUpdater
 * Конкретный Observer - Обновление sitemap
 *
 * @package Behavioral\Observer
 */
class SitemapUpdater implements Observer
{
    public function update(Subject $subject): void
    {
        if ($subject instanceof BlogPost && $subject->isPublished()) {
            // do something,SitemapUpdater update-able, instead current stub.

            echo "[Sitemap] Updating sitemap with new post: '{$subject->getTitle()}'\n";
        }
    }
}

/**
 * Class Logger
 * Конкретный Observer - Логирование
 *
 * @package Behavioral\Observer
 */
class Logger implements Observer
{
    /**
     * @param Subject $subject
     */
    public function update(Subject $subject): void
    {
        if ($subject instanceof BlogPost) {
            // Updating for BlogPost per logger

            echo "[Logger] Post updated: '{$subject->getTitle()}'"
                . ($subject->isPublished() ? ' [PUBLISHED]' : '') . "\n";
        }
    }
}

/**
 * Class EventManager
 * Пример 2: Реальная система событий
 *
 * @package Behavioral\Observer
 */
class EventManager implements Subject
{
    /** @var array<string, list<Observer>> */
    private array $listeners = [];

    /**
     * @param Observer $observer
     * @param string $event
     */
    public function attach(Observer $observer, string $event): void
    {
        $this->listeners[$event][] = $observer;
    }

    /**
     * @param Observer $observer
     * @param string $event
     */
    public function detach(Observer $observer, string $event): void
    {
        if (isset($this->listeners[$event])) {
            $this->listeners[$event] = array_filter(
                $this->listeners[$event],
                fn ($obs) => $obs !== $observer
            );
        }
    }

    /**
     * @param string $event
     * @param mixed|null $data
     */
    public function notify(string $event, mixed $data = null): void
    {
        if (isset($this->listeners[$event])) {
            // do some listener, to notify.

            echo "Event '{$event}' triggered. Notifying listeners...\n";

            foreach ($this->listeners[$event] as $observer) {
                // Создаем анонимный субъект для события
                $subject = new class($event, $data) implements Subject {
                    public function __construct(
                        private string $event,
                        private mixed  $data
                    ) {
                    }

                    public function attach(Observer $observer): void
                    {
                    }

                    public function detach(Observer $observer): void
                    {
                    }

                    public function notify(): void
                    {
                    }

                    public function getEvent(): string
                    {
                        return $this->event;
                    }

                    public function getData(): mixed
                    {
                        return $this->data;
                    }
                };

                $observer->update($subject);
            }
        }
    }
}

/**
 * Class AnalyticsTracker
 * Конкретный Observer для EventManager
 *
 * @package Behavioral\Observer
 */
class AnalyticsTracker implements Observer
{
    public function update(Subject $subject): void
    {
        if (method_exists($subject, 'getEvent')) {
            $event = $subject->getEvent();
            $data = method_exists($subject, 'getData')
                ? $subject->getData()
                : null;

            echo "[Analytics] Tracking event: {$event} with data: "
                . json_encode($data) . "\n";
        }
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    Subject (BlogPost) - управляет списком наблюдателей
 *    Observer (EmailNotification) - определяет метод update() для реакции на изменения
 *    При изменении - Subject вызывает notify(), который оповещает всех Observer
 *    Observer получает Subject и может получить его состояние
 *
 * Преимущества:
 *    Слабая связанность - Subject не знает о конкретных Observer
 *    Гибкость - легко добавлять/удалять Observer
 *    Переиспользование - Subject и Observer можно независимо изменять
 *    Расширяемость - можно добавлять новые типы Observer без изменения Subject
 * Недостатки:
 *    Производительность - уведомление всех Observer может быть дорогим
 *    Память - хранение ссылок на Observer
 *    Неопределенность - Observer могут быть уведомлены в непредсказуемом порядке
 *    Утечки памяти - необходимо явно удалять Observer
 *
 * Где используется в фреймворках:
 * 1. Laravel Events
 *    // app/Listeners/SendWelcomeEmail.php
 *    class SendWelcomeEmail
 *    {
 *        public function handle(UserRegistered $event)
 *        {
 *            // Отправка email при регистрации пользователя
 *        }
 *    }
 *    // Событие вызывает всех слушателей
 *    event(new UserRegistered($user));
 * 2. Symfony EventDispatcher
 *    $dispatcher = new EventDispatcher();
 *    $dispatcher->addListener('kernel.request', function(FilterControllerEvent $event) {
 *        // Логирование запросов
 *    });
 *    // Посредник dispatch уведомляет всех слушателей
 *    $dispatcher->dispatch(new FilterControllerEvent(...), 'kernel.request');
 * 3. WordPress Hooks
 *    // add_action - регистрация наблюдателя
 *    add_action('user_register', function($user_id) {
 *        // Отправка приветственного email
 *    });
 *    // do_action - уведомление наблюдателей
 *    do_action('user_register', $user_id);
 * 4. Doctrine ORM
 *    $eventManager = new EventManager();
 *    $eventManager->addEventListener(['preUpdate'], new MyListener());
 *    // Посредник dispatch уведомляет слушателей перед обновлением
 *    $eventManager->dispatchEvent('preUpdate', $eventArgs);
 * 5. Yii2 Event Handling
 *    class User extends Component
 *    {
 *        const EVENT_LOGIN = 'login';
 *        public function login()
 *        {
 *            // ... логика аутентификации
 *            $this->trigger(self::EVENT_LOGIN);
 *        }
 *    }
 *    // Наблюдатель подписывается на событие
 *    $user->on(User::EVENT_LOGIN, function($event) {
 *    // Отправка лога
 *    });
 * 6. ReactPHP Event Loop (https://github.com/reactphp/event-loop#addtimer)
 *    $loop = Factory::create();
 *    $loop->addTimer(1.0, function () {
 *        echo "Timer expired!\n";
 *    });
 *    // Наблюдатель (callback) ждет событие (timer expiration) (non-blocking I/O on stream_set_blocking($stream, false) func)
 *
 * Когда полезен:
 * Системы событий - обработка бизнес-процессов
 * UI Frameworks - реакция на действия пользователя (клики, ввод)
 * Многопоточность - оповещение потоков о завершении задач
 * Игры - реакция на игровые события (уничтожение, смена уровеня итп)
 * Мониторинг - уведомления об изменениях состояния
 *
 */

echo "=== Blog Post Example(v1) ===\n";
$post = new BlogPost('Design Patterns', 'Learning about Observer pattern...');
$email = new EmailNotification();
$sitemap = new SitemapUpdater();
$logger = new Logger();

$post->attach($email);
$post->attach($sitemap);
$post->attach($logger);

$post->publish();
// ---------------------------------

echo "\n=== Event Manager Example(v2) ===\n";
$events = new EventManager();
$tracker = new AnalyticsTracker();

$events->attach($tracker, 'user.login');
$events->attach($tracker, 'user.purchase');

$events->notify('user.login', ['user_id' => 123]);
$events->notify('user.purchase', ['amount' => 49.99]);

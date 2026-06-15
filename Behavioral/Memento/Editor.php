<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 15.06.2026 - 20:56
 */

namespace Behavioral\Memento;

/**
 * Class EditorMemento
 * Пример 1: Memento - хранитель состояния
 *
 * @package Behavioral\Memento
 */
class EditorMemento
{
    /**
     * Используем private property promotion для инкапсуляции
     */
    public function __construct(
        private string  $state,
        private ?string $metadata = null
    )
    {
    }

    /**
     * Только Originator может получить состояние
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getMetadata(): ?string
    {
        return $this->metadata;
    }
}

/**
 * Class TextEditor
 * Originator - объект, состояние которого сохраняется
 *
 * @package Behavioral\Memento
 */
class TextEditor
{
    private string $content = '';
    private string $font = 'Arial';
    private int $size = 12;
    private array $snapshots = [];

    public function __construct(
        private string $filename
    )
    {
    }

    /**
     * @param string $text
     */
    public function type(string $text): void
    {
        $this->content .= $text;
        $this->snapshots[] = $this->createMemento();
    }

    /**
     * @return EditorMemento
     */
    private function createMemento(): EditorMemento
    {
        return new EditorMemento(
            serialize(
                [
                    'content' => $this->content,
                    'font' => $this->font,
                    'size' => $this->size
                ]
            ),
            date('Y-m-d H:i:s')
        );
    }

    /**
     * @return EditorMemento
     */
    public function save(): EditorMemento
    {
        return $this->createMemento();
    }

    /**
     * @param EditorMemento $memento
     */
    public function restore(EditorMemento $memento): void
    {
        $state = unserialize($memento->getState());
        $this->content = $state['content'];
        $this->font = $state['font'];
        $this->size = $state['size'];
        echo "Restored from {$memento->getMetadata()}\n";
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}

/**
 * Class EditorHistory
 * Caretaker - хранитель истории
 *
 * @package Behavioral\Memento
 */
class EditorHistory
{
    /** @var EditorMemento[] */
    private array $mementos = [];
    private int $position = 0;

    /**
     * @param EditorMemento $memento
     */
    public function addMemento(EditorMemento $memento): void
    {
        $this->mementos[] = $memento;
        $this->position = count($this->mementos) - 1;
    }

    /**
     * @param TextEditor $editor
     */
    public function undo(TextEditor $editor): void
    {
        if ($this->position > 0) {
            $this->position--;
            $editor->restore($this->mementos[$this->position]);
        }
    }

    /**
     * @param TextEditor $editor
     */
    public function redo(TextEditor $editor): void
    {
        if ($this->position < count($this->mementos) - 1) {
            $this->position++;
            $editor->restore($this->mementos[$this->position]);
        }
    }

    /**
     * @return array
     */
    public function getHistory(): array
    {
        return array_map(
            fn($m, $i) => "{$i}: " . $m->getMetadata(), $this->mementos
        );
    }
}

/**
 * Class ConfigMemento
 * Пример 2: Сохранение конфигурации
 *
 * @package Behavioral\Memento
 */
class ConfigMemento
{
    public function __construct(
        private array  $config,
        private string $version
    )
    {
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}

/**
 * Class ApplicationConfig
 * @package Behavioral\Memento
 */
class ApplicationConfig
{
    private array $settings = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function updateSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    /**
     * @return ConfigMemento
     */
    public function saveState(): ConfigMemento
    {
        return new ConfigMemento($this->settings, 'v1.0.0');
    }

    /**
     * @param ConfigMemento $memento
     */
    public function restore(ConfigMemento $memento): void
    {
        $this->settings = $memento->getConfig();

        echo "Config restored to version {$memento->getVersion()}\n";
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Memento (EditorMemento) - хранит состояние Originator в инкапсулированном виде
 *    Originator (TextEditor) - создает Memento и восстанавливает из него состояние
 *    Caretaker (EditorHistory) - хранит Memento, но не имеет доступа к его содержимому
 *    Инкапсуляция - только Originator может получить доступ к состоянию Memento
 *
 * Преимущества:
 *    Инкапсуляция - состояние хранится вне Originator, но доступно только ему
 *    Простота отмены/повтора - легко реализовать undo/redo
 *    Сохранность данных - можно сохранять состояние в критических точках
 *    Архивация - хранение истории изменений
 *
 * Недостатки:
 *    Память - хранение множества состояний может потреблять много памяти
 *    Сложность - глубокое копирование сложных объектов может быть дорогим
 *    Сериализация - необходимо сериализовать состояние для хранения
 *
 * Где используется в фреймворках:
 * 1. Laravel Eloquent Models
 *    // Модель может быть "восстановлена" после удаления
 *    $user = User::find(1);
 *    $user->delete(); // Мягкое удаление (soft delete)
 *    $user->restore(); // Восстановление из "хранителя"
 * 2. Symfony Workflow Component
 *    $workflow = new Workflow();
 *    $place = $workflow->getMarking($subject);
 *    // Сохранение состояния workflow как "моменто"
 *    $marking = $workflow->getMarking($subject);
 *    // Восстановление состояния
 *    $workflow->setMarking($subject, $marking);
 * 3. Doctrine ORM
 *    // Flush создает "моменто" текущего состояния
 *    $entityManager->flush(); // Сохраняет состояние в базу
 *    // Rollback восстанавливает состояние
 *    $entityManager->rollback(); // Восстанавливает предыдущее состояние
 * 4. PHP classic serialize()/unserialize()
 *    // Встроенная реализация Memento
 *    $original = new User();
 *    $serialized = serialize($original); // Memento
 *    $restored = unserialize($serialized); // Восстановление
 * 5. Laravel's Form Request Validation
 *    // Сохранение состояния запроса для повторной валидации
 *    $request->flash(); // Сохраняет данные формы
 *    $request->old(); // Восстанавливает старое состояние
 * 6. Symfony's Form Component
 *    $form = $this->createFormBuilder($task)->getForm();
 *    $form->handleRequest($request);
 *    if ($form->isSubmitted() && $form->isValid()) {
 *        // Сохранение состояния
 *        $data = $form->getData();
 *    }
 *    // Восстановление состояния(заполнение старыми данными)
 *    $form->setData($oldData);
 *
 * Когда полезен:
 *    Текстовые редакторы - undo/redo, сохранение документов
 *    Игры - сохранения игры, загрузка состояния
 *    Браузеры - кнопка "назад", история вкладок
 *    Базы данных - транзакции, rollback
 *    Конфигурации - версионирование настроек
 *    API - сохранение состояния запроса
 *
 */

echo "=== Text Editor Example(v1) ===\n";
$editor = new TextEditor('document.txt');
$history = new EditorHistory();

$editor->type('Hello, ');
$history->addMemento($editor->save());

$editor->type('World!');
$history->addMemento($editor->save());

echo "Current content: {$editor->getContent()}\n"; // Hello, World!

$history->undo($editor);
echo "After undo: {$editor->getContent()}\n"; // Hello,

$history->redo($editor);
echo "After redo: {$editor->getContent()}\n"; // Hello, World!


echo "\n=== Config Example(v2) ===\n";
$config = new ApplicationConfig();
$config->updateSetting('debug_mode', true);
$config->updateSetting('cache_ttl', 3600);

$memento = $config->saveState();
$config->updateSetting('debug_mode', false);

echo "Debug mode before restore: " . ($config->settings['debug_mode'] ? 'true' : 'false') . "\n";

$config->restore($memento);
echo "Debug mode after restore: " . ($config->settings['debug_mode'] ? 'true' : 'false') . "\n";

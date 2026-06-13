<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 13.06.2026 - 18:30
 */

namespace Behavioral\Command;

/**
 * Interface Command
 * Интерфейс Команды определяет метод execute()
 *
 * @package Behavioral\Command
 */
interface Command
{
    public function execute(): void;
}

/**
 * Class LogCommand
 * Конкретная команда для логгирования
 *
 * @package Behavioral\Command
 */
class LogCommand implements Command
{
//    private string $message;
//    private Logger $logger;

    public function __construct(
        public readonly Logger $logger,
        public readonly string $message
    )
    {
        // property promotion уже используется в сигнатуре конструктора
    }

    public function execute(): void
    {
        $this->logger->log($this->message);
    }
}

/**
 * Class Logger
 * Получатель (Receiver) - выполняет реальную работу
 *
 * @package Behavioral\Command
 */
class Logger
{
    public function log(string $message): void
    {
        // Простое логгирование в файл
        file_put_contents(
            __DIR__ . '/../../logs/app.log',
            date('Y-m-d H:i:s') . " - {$message}\n",
            FILE_APPEND
        );
        echo "Logged: {$message}\n";
    }
}

/**
 * Class CommandQueue
 * Отправитель (Invoker) - управляет выполнением команд
 *
 * @package Behavioral\Command
 */
class CommandQueue
{
    /** @var Command[] */
    private array $commands = [];

    public function addCommand(Command $command): void
    {
        $this->commands[] = $command;
    }

    public function run(): void
    {
        foreach ($this->commands as $command) {
            $command->execute();
        }
        $this->commands = []; // Очищаем очередь после выполнения
    }
}


/**
 * Клиентский код
 *
 * Как это работает:
 *    LogCommand - конкретная команда, которая инкапсулирует запрос на логгирование
 *    Logger - получатель, который содержит реальную логику логгирования
 *    CommandQueue - отправитель/очередь, который управляет выполнением команд
 *    Command - интерфейс, обеспечивающий единообразие всех команд
 *
 * Ключевые преимущества:
 *    Инкапсуляция запросов - каждая операция логгирования становится отдельным объектом
 *    Очереди - легко добавлять, удалять или откладывать выполнение команд
 *    Расширяемость - можно легко добавить новые типы команд (например, EmailCommand, CacheCommand)
 *    Отмена операций - можно добавить метод undo() в интерфейс Command
 *
 * Использование:
 *    Системах очередей задач (Laravel Queues, Symfony Messenger)
 *    Истории действий (Undo/Redo)
 *    Асинхронной обработке событий
 *    Логгировании с разными уровнями важности
 *
 */
$logger = new Logger();
$queue = new CommandQueue();

// Создаем команды
$queue->addCommand(new LogCommand($logger, 'User logged in'));
$queue->addCommand(new LogCommand($logger, 'Payment processed'));
$queue->addCommand(new LogCommand($logger, 'Order shipped'));

// Выполняем все команды из очереди
$queue->run();

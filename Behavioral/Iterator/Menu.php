<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 14.06.2026 - 18:19
 */

namespace Behavioral\Iterator;

use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * Class Menu
 * Конкретная коллекция - меню ресторана
 *
 * @package Behavioral\Iterator
 */
class Menu implements IteratorAggregate
{
    /**
     * Menu constructor.
     * Используем конструктор property promotion
     * @param array $items
     */
    public function __construct(
        private array $items = []
    )
    {
    }

    /**
     * @param string $name
     * @param float $price
     */
    public function addItem(string $name, float $price): void
    {
        $this->items[] = ['name' => $name, 'price' => $price];
    }

    /**
     * Возвращаем внешний итератор
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new class($this->items) implements Iterator {
            public function __construct(
                private array $items,
                private int   $position = 0
            )
            {
            }

            public function current(): mixed
            {
                return $this->items[$this->position];
            }

            public function next(): void
            {
                ++$this->position;
            }

            public function key(): mixed
            {
                return $this->position;
            }

            public function valid(): bool
            {
                return isset($this->items[$this->position]);
            }

            public function rewind(): void
            {
                $this->position = 0;
            }
        };
    }
}

/**
 * Class AdvancedMenu
 * Альтернативный вариант - внутренний итератор (если нужна сложная логика)
 *
 * @package Behavioral\Iterator
 */
class AdvancedMenu implements Iterator
{
    public function __construct(
        private array $items,
        private int   $position = 0
    )
    {
    }

    /**
     * @return array
     */
    public function current(): array
    {
        return $this->items[$this->position];
    }

    /**
     * next
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * rewind (set to 0)
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}

/**
 * Клиентский код
 *
 * Как это работает:
 *    Menu реализует IteratorAggregate и возвращает внешний итератор через getIterator()
 *    Анонимный класс внутри getIterator() реализует Iterator интерфейс
 *    PHP автоматически вызывает методы итератора при использовании foreach
 *
 * Преимущества:
 *    Единый интерфейс - клиент не знает о внутреннем устройстве коллекции
 *    Производительность - можно реализовать ленивую загрузку (lazy loading)
 *    Гибкость - можно создавать разные итераторы для одной коллекции
 *    Читаемость - foreach работает с любым итерируемым объектом
 *
 *    Где используется в фреймворках:
 * 1. Laravel Collections
 *    $collection = collect([1, 2, 3]);
 *    foreach ($collection as $item) {
 *    // Автоматически использует Iterator
 *    }
 * 2. Doctrine ORM
 *    $users = $entityManager->getRepository(User::class)->findAll();
 *    foreach ($users as $user) {
 *    // Использует Iterator для ленивой загрузки
 *    }
 * 3. Symfony Validator
 *    foreach ($validator->validate($data, $constraints)  as $violation) {
 *         // Итерация по нарушениям/валидации
 *    }
 * 4. Twig Templates
 *    {% for item in menu %} {{ item.name }} - {{ item.price }} {% endfor %}
 *    // Автоматически использует Iterator
 * 5. WordPress WP_Query
 *    $query = new WP_Query($args);
 *    foreach ($query as $post) {
 *    // Итерация по постам
 *    }
 *
 * Ключевые моменты для PHP 8.4:
 *    IteratorAggregate если нужен внешний итератор
 *    Реализуйте Iterator напрямую если нужен сложный итератор с состоянием
 *    Анонимные классы отлично подходят для простых итераторов
 *    foreach работает с любым объектом, реализующим \Traversable (Iterable|array)
 *
 */
$menu = new Menu();
$menu->addItem('Pizza Margherita', 12.99);
$menu->addItem('Spaghetti Carbonara', 10.99);
$menu->addItem('Tiramisu', 6.99);

// Использование в foreach - магия PHP!
foreach ($menu as $index => $item) {
    echo "{$index}: {$item['name']} - \${$item['price']}\n";
}

// Или с AdvancedMenu
$advancedMenu = new AdvancedMenu(
    [
        ['name' => 'Pizza', 'price' => 12.99],
        ['name' => 'Pasta', 'price' => 10.99],
    ]
);

foreach ($advancedMenu as $item) {
    echo "{$item['name']} - \${$item['price']}\n";
}

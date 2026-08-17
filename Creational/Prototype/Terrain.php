<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.09.19 - 09:11
 * upd: 18.08.26 - 18:31
 */

namespace Creational\Prototype;

abstract class Sea
{
}

class EarthSea extends Sea
{
}

class MarsSea extends Sea
{
}

abstract class Plains
{
    // PHP 8+: Объявление свойства прямо в конструкторе
    public function __construct(private int $visibility = 1)
    {
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }
}

class EarthPlains extends Plains
{
}

class MarsPlains extends Plains
{
}

abstract class Lowlands
{
}

class EarthLowlands extends Lowlands
{
}

class MarsLowlands extends Lowlands
{
}

/**
 * Class TerrainFactory - основной класс для быстрого создания(клонов) необходимых классов.
 * @package Creational\Prototype
 */
class TerrainFactory
{
    // PHP 8+: Продвижение свойств конструктора (Constructor Property Promotion)
    public function __construct(
        private Sea $sea,
        private Plains $plains,
        private Lowlands $lowlands
    ) {
    }

    /**
     * Get getSea
     * @return Sea
     */
    public function getSea(): Sea
    {
        return clone $this->sea;
    }

    /**
     * Get getPlains
     * @return Plains
     */
    public function getPlains(): Plains
    {
        return clone $this->plains;
    }

    /**
     * Get getLowlands
     * @return Lowlands
     */
    public function getLowlands(): Lowlands
    {
        return clone $this->lowlands;
    }
}


// Простой пример клиентского кода:

echo "--- Создаем фабрику для Земли (Earth) ---\n";
$earthFactory = new TerrainFactory(
    new EarthSea(),
    new EarthPlains(2),
    new EarthLowlands()
);

echo "--- Объекты Земли(авто копирование) ---\n";
var_dump($earthFactory->getSea());
var_dump($earthFactory->getPlains());
var_dump($earthFactory->getLowlands());

// Создаем фабрику для Марса (Mars)
$marsFactory = new TerrainFactory(
    new MarsSea(),
    new MarsPlains(5),
    new MarsLowlands()
);

echo "\n--- Объекты Марса (Mars) ---\n";
var_dump($marsFactory->getSea());

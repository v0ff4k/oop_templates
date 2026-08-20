<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 * upd: 06.08.2026 - 12:18
 * upd: 20.08.2026 - 20:35  make real FactoryMethod +DependencyInversion
 */

namespace Creational\FactoryMethod;

/**
 * Class ApptEncoder
 * @package Creational\FactoryMethod
 */
abstract class ApptEncoder
{
    abstract public function encode(): string;
}

/**
 * Class CommsManager
 *
 * @package Creational\FactoryMethod
 */
abstract class CommsManager
{
    // Factory Method - создает объект продукта
    abstract public function getApptEncoder(): ApptEncoder;

    // Дополнительные методы (не обязательны для шаблона)
    public function getHeaderText(): string
    {
        return '';
    }

    public function getFooterText(): string
    {
        return '';
    }
}

/**
 * Class BloggsAptEncoder - Конкретный продукт
 *
 * @package Creational\FactoryMethod
 */
class BloggsAptEncoder extends ApptEncoder
{
    public function encode(): string
    {
        return "Encode by BloggsCal \n";
    }
}

/**
 * Class BloggsCommsManager - Конкретный создатель
 *
 * @package Creational\FactoryMethod
 */
class BloggsCommsManager extends CommsManager
{
    // Factory Method реализация - создает конкретный продукт
    public function getApptEncoder(): ApptEncoder
    {
        return new BloggsAptEncoder();
    }

    public function getHeaderText(): string
    {
        return "BloggsCal header \n";
    }

    public function getFooterText(): string
    {
        return "BloggsCal footer \n";
    }
}

/**
 * Class MegaAptEncoder - Другой конкретный продукт
 *
 * @package Creational\FactoryMethod
 */
class MegaAptEncoder extends ApptEncoder
{
    public function encode(): string
    {
        return "Encode by MegaCal \n";
    }
}

/**
 * Class MegaCommsManager - Другой конкретный создатель
 *
 * @package Creational\FactoryMethod
 */
class MegaCommsManager extends CommsManager
{
    //Factory Method реализация - создает другой конкретный продукт
    public function getApptEncoder(): ApptEncoder
    {
        return new MegaAptEncoder();
    }

    public function getHeaderText(): string
    {
        return "MegaCal header \n";
    }

    public function getFooterText(): string
    {
        return "MegaCal footer \n";
    }
}


// Клиентский код - работает с абстракциями
function renderOutput(CommsManager $manager): void
{
    echo $manager->getHeaderText();

    // Фабричный метод обеспечивает инверсию зависимостей
    $encoder = $manager->getApptEncoder();
    echo $encoder->encode();

    echo $manager->getFooterText();
    echo "---------------------------\n";
}

// Демонстрация работы
echo "=== Тестируем BloggsCal ===\n";
$bloggsManager = new BloggsCommsManager();
renderOutput($bloggsManager);

echo "=== Тестируем MegaCal ===\n";
$megaManager = new MegaCommsManager();
renderOutput($megaManager);

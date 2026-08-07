<?php

declare(strict_types=1);

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 * upd: 06.08.2026 - 12:18
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
 * @package Creational\FactoryMethod
 */
abstract class CommsManager
{
    abstract public function getHeaderText(): string; //just for example

    abstract public function getApptEncoder(): ApptEncoder; //must return only ApptEncoder

    abstract public function getFooterText(): string; //just for example
}

/**
 * Class BloggsAptEncoder - example of realisation
 * @package Creational\FactoryMethod
 */
class BloggsAptEncoder extends ApptEncoder
{
    public function encode(): string
    {
        return "Encode by BloggsCal \n";
    }
}


class BloggsCommsManager extends CommsManager
{
    public function getHeaderText(): string
    {
        return "BloggsCal header \n";
    }

    /** @return ApptEncoder|BloggsAptEncoder */
    public function getApptEncoder(): BloggsAptEncoder
    {
        return new BloggsAptEncoder();
    }

    public function getFooterText(): string
    {
        return "BloggsCal footer \n";
    }
}

/**
 * Class MegaAptEncoder - example of extra realisation, by adding new  "ENCODER"
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
 * Class MegaCommsManager - and add new  "MANAGER" for encoder
 *
 * @package Creational\FactoryMethod
 */
class MegaCommsManager extends CommsManager
{
    public function getHeaderText(): string
    {
        return "MegaCal header \n";
    }

    /** @return ApptEncoder|MegaAptEncoder */
    public function getApptEncoder(): MegaAptEncoder
    {
        return new MegaAptEncoder();
    }

    public function getFooterText(): string
    {
        return "MegaCal footer \n";
    }
}


// --- Клиентский код, указываем нужные  "менеджеры" ---
// use Creational\FactoryMethod\CommsManager;
// use Creational\FactoryMethod\BloggsCommsManager;
// use Creational\FactoryMethod\MegaCommsManager;

/**
 * Универсальная функция(Factory Method) для рендеринга данных.
 * Ей абсолютно всё равно, какой именно менеджер пришел на вход.
 */
function renderOutput(CommsManager $manager): void
{
    // 1. Выводим заголовок
    echo $manager->getHeaderText();

    // 2. Запрашиваем фабричный метод создать кодировщик
    $encoder = $manager->getApptEncoder();

    // 3. Кодируем данные
    echo $encoder->encode();

    // 4. Выводим футер
    echo $manager->getFooterText();
    echo "---------------------------\n";
}

// --- Демонстрация работы ---

// Ситуация 1: Работаем с системой Bloggs
echo "=== Тестируем BloggsCal ===\n";
$bloggsManager = new BloggsCommsManager();
renderOutput($bloggsManager);

// Ситуация 2: Легко переключаемся на систему Mega
echo "=== Тестируем MegaCal ===\n";
$megaManager = new MegaCommsManager();
renderOutput($megaManager);

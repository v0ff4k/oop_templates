<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 02.10.19 - 9:17
 */

namespace Behavioral\Observer;

interface Observer
{
    public function notify($object);
}

/**
 * Class ExchangeRate
 *
 * Standard single tone on "steroids"
 * @package Behavioral\Observer
 */
class ExchangeRate
{
    /** @var ExchangeRate $instance */
    private static $instance = null;

    /** @var array of Observer $observers */
    private $observers = [];

    /** @var float $exchangeRate */
    private $exchangeRate;

    private function __construct()
    {
        //forbid creation multiple instances
    }

    private function __clone()
    {
        //forbid to clone
    }

    /**
     * Get getInstance
     * @return null|\Behavioral\Observer\ExchangeRate
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new ExchangeRate();
        }
        return self::$instance;
    }

    /**
     * Get getExchangeRate
     * @return mixed
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * Set ExchangeRate
     * @param mixed $exchangeRate
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
        $this->notifyObservers();
    }

    public function registerObserver(Observer $object)
    {
        $this->observers[] = $object;
    }

    public function notifyObservers()
    {
        /** @var Observer $observer */
        foreach ($this->observers as $observer) {
            $observer->notify($this);
        }
    }
}

class ProductItem implements Observer
{
    public function __construct()
    {
        // alternative to   parent::__construct();
        ExchangeRate::getInstance()->registerObserver($this);
    }

    public function notify($object)
    {
        if ($object instanceof ExchangeRate) {
            print_r('Update received for new product item');
        }
    }
}

// client code::::
$productOne = new ProductItem();
$productTwo = new ProductItem();
$productThree = new ProductItem();

// and set exchange rate for that products
ExchangeRate::getInstance()->setExchangeRate(3.5);

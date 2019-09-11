<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 */

// for disable   new ReflectionClass("") replace "class" with "trait" !
class Preferences
{
    /** @var self */
    private static $instance;
    private $properties = array();

    //forbid to create "new" instance  with finalize
    private final function __construct() {}
    //forbid clone for create another instance  with finalize
    private final function __clone() {}
    //forbid *serialize() for create another instance  with finalize
    private final function __wakeup() {}

    public static function getInstance() {
        if ((empty(static::$instance))) {
            static::$instance = new Preferences();
        }

        return static::$instance;
    }

    public function getProperty($key) {
        return $this->properties[$key];
    }

    public function setProperty($key, $value) {
        $this->properties[$key] = $value;
    }
}

//client code:::
$dataPref = Preferences::getInstance();
$dataPref->setProperty('database', "mySQL");

unset($dataPref);

$anotherPref = Preferences::getInstance();
$value =  $anotherPref->getProperty('database');
print $value . " connected \n";// mySQL connected
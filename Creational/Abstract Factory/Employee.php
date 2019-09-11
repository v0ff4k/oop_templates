<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 */

abstract class Employee
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    abstract function fire();
}

class Minion extends Employee
{
    public function fire() {
        print $this->name . ", do Minion work! \n";
    }
}

class Clued extends Employee
{
    function fire() {
        //much slower function than print
        printf("%s, call a lower \n", $this->name);
    }
}

class NastyBoss
{
    private $employees = [];

    function addEmployee(Employee $employee) {
        $this->employees[] = $employee;
    }

    function projectFails() {
        if (count($this->employees) > 0) {
            /** @var \Employee $latestEmployee */
            $latestEmployee = array_pop($this->employees);
            $latestEmployee->fire();
        }
    }
}


//client code:::  with example of polymorphism
$oBoss = new NastyBoss();
$oBoss->addEmployee( new Minion('John'));
$oBoss->addEmployee( new Clued('Ivan'));
$oBoss->addEmployee( new Minion('Mari'));
$oBoss->projectFails(); //Mari, do Minion work
$oBoss->projectFails(); //Ivan, call a lower
$oBoss->projectFails(); //John, do Minion work



<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 09.09.19 - 09:09
 */

abstract class Lesson
{
    private  $duration;
    private  $costStrategy;

    public function __construct($duration, CostStrategy $strategy)
    {
        $this->duration = $duration;
        $this->costStrategy = $strategy;
    }

    //delegated call
    function cost()
    {
        return $this->costStrategy->cost($this);
    }

    //delegated call
    function chargeType()
    {
        return $this->costStrategy->chargeType();
    }

    function getDuration()
    {
        return $this->duration;
    }
}

class Lecture extends Lesson
{
    // some spec realisation of Lectures
}

class Seminar extends Lesson
{
    // some spec realisation of Seminars
}

//main strategy that will be extend
abstract class CostStrategy
{
    abstract function cost(Lesson $lesson);
    abstract function chargeType();
}

class TimedCostStrategy extends CostStrategy
{
    public function cost(Lesson $lesson)
    {
        return ($lesson->getDuration() * 5);
    }

    public function chargeType()
    {
        return "Pay per hour";
    }
}

class FixedCostStrategy extends CostStrategy
{
    public function cost(Lesson $lesson)
    {
        return 20;
    }

    public function chargeType()
    {
        return "Fixed price";
    }
}


// polymorphism in the action, the client code:::
$lessons[] = new Seminar(2, new TimedCostStrategy());
$lessons[] = new Lecture(8, new FixedCostStrategy());
$lessons[] = new Seminar(4, new FixedCostStrategy());
$lessons[] = new Lecture(2, new TimedCostStrategy());

/** @var \Lesson $lesson */
foreach ($lessons as $lesson) {

    print "pay per lesson: " . $lesson->cost() . ". ";
    print "pay type: " . $lesson->chargeType() . " \n ";
}
<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 09.09.19 - 09:09
 */

abstract class Tile
{
    abstract function getWealthFactor();
}

class Plains extends Tile
{
    private $wealthFactor = 2;

    function getWealthFactor()
    {
        return $this->wealthFactor;
    }
}

abstract class TileDecorator extends Tile
{
    protected $tile;
    public function __construct(Tile $tile)
    {
        $this->tile = $tile;
    }
}


//extend code:::

class DiamondDecorator extends TileDecorator
{
    public function getWealthFactor()
    {
        return $this->tile->getWealthFactor() + 2;
    }
}

class PollutionDecorator extends TileDecorator
{
    public function getWealthFactor()
    {
        return $this->tile->getWealthFactor() - 3;
    }
}

class ForestDecorator extends TileDecorator
{
    public function getWealthFactor()
    {
        return $this->tile->getWealthFactor() + 3;
    }
}


//client code:::

$title1 = new Plains();
var_dump($title1->getWealthFactor()); //2

$title2 = new ForestDecorator( new  Plains() );// 2 + 3
var_dump($title2->getWealthFactor());// 5

// -2  +3  +2  +2 ???
$title3 = new PollutionDecorator( new ForestDecorator( new DiamondDecorator( new Plains())));
var_dump($title3->getWealthFactor());// =5?
<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 19.09.19 - 09:11
 */

namespace Creational\Prototype;

class Sea {}
class EarthSea extends Sea {}
class MarsSea extends Sea {}

class Plains
{
    private $visibility = 1;

    public function __construct($visibility)
    {
        $this->visibility = $visibility;
    }
}
class EarthPlains extends Plains {}
class MarsPlains extends Plains {}

class Lowlands {}
class EarthLowlands extends Lowlands {}
class MarsLowlands extends Lowlands {}

class TerrainFactory
{
    private $sea;
    private $plains;
    private $lowlands;

    public function __construct(Sea $sea, Plains $plains, Lowlands $lowlands)
    {
        $this->sea = $sea;
        $this->plains = $plains;
        $this->lowlands = $lowlands;
    }

    /**
     * Get getSea
     * @return Sea
     */
    public function getSea()
    {
        return clone $this->sea;
    }

    /**
     * Get getPlains
     * @return Plains
     */
    public function getPlains()
    {
        return clone  $this->plains;
    }

    /**
     * Get getLowlands
     * @return Lowlands
     */
    public function getLowlands()
    {
        return clone  $this->lowlands;
    }
}


///client code:::

$factory = new TerrainFactory(new EarthSea(), new EarthPlains(2), new MarsLowlands());
var_dump($factory->getLowlands());
var_dump($factory->getPlains());
var_dump($factory->getSea());

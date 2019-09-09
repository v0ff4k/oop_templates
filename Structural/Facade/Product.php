<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 09.09.19 - 09:09
 */

//older legacy code that we explain in Facade
class Product {
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}


function getProductFileLines($file) {

    return file($file);
}

function getProductObjectFromId($id, $productName) {
    //implement a request into DB or other, where all data are stored
    return new Product($id, $productName);
}

function getNameFromLine($line) {
    if (preg_match("/.*-(.*)\s\d+/", $line, $array)) {

        return str_replace('_',  ' ', $array[1]);
    }

    return '';
}

function getIdFromLine($line) {
    if (preg_match("/^(\d{1,3})-/", $line, $array)) {
        return $array[1];
    }
    return -1;
}
//end LEGACY  code that we must explain


//new stuff that makes  readable request  from client code
class ProductFacade
{
    private $products = [];

    public function __construct($file)
    {
        $this->file = $file;
        $this->compile();
    }

    private function compile() {

        $lines = getProductFileLines($this->file);
        foreach ($lines as $line) {
            $id = getIdFromLine($line);
            $name = getNameFromLine($line);
            $this->products[$id] = getProductObjectFromId($id, $name);
        }
    }

    function getProducts() {
        return $this->products;
    }

    function getProduct($id) {
        return $this->products[$id];
    }

}

//input parameters
$file = 'product-facade.txt';
$idNeeded = 123;

// old client code::
$lines = getProductFileLines($file);
$products = [];
foreach ($lines as $line) {
    $id = getIdFromLine($line);
    $name = getNameFromLine($line);
    $products[$id] = getProductObjectFromId($id, $name);
}
$product =  $products[$idNeeded];


// easy client code with Facade::
$facade = new ProductFacade($file);
$product = $facade->getProduct($idNeeded);
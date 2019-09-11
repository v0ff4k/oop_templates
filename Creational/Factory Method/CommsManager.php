<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 10.09.19 - 10:09
 */

abstract class ApptEncoder
{
    abstract function  encode();
}

abstract class CommsManager
{
    abstract function getHeaderText(); //just for example
    abstract function getApptEncoder(); //must return only ApptEncoder
    abstract function getFooterText(); //just for example
}

//example of realisation
class BloggsAptEncoder extends ApptEncoder
{
    public function encode()
    {
        return "Encode by BloggsCal \n";
    }
}


class BloggsCommsManager extends CommsManager
{
    public function getHeaderText()
    {
        return "BloggsCal header \n";
    }

    public function getApptEncoder()
    {
        return new BloggsAptEncoder();
    }

    public function getFooterText()
    {
        return "BloggsCal footer \n";
    }
}

//example of extra realisation, by

// adding new  "ENCODER"
class MegaAptEncoder extends ApptEncoder
{
    public function encode()
    {
        return "Encode by MegaCal \n";
    }
}

// and add new  "MANAGER" for encoder
class MegaCommsManager extends CommsManager
{
    public function getHeaderText()
    {
        return "MegaCal header \n";
    }

    public function getApptEncoder()
    {
        return new MegaAptEncoder();
    }

    public function getFooterText()
    {
        return "MegaCal footer \n";
    }
}


<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 12.09.19 - 12:09
 *
 * Creating series of related or dependent objects without specifying their concrete classes.
 */

abstract class CommunicationManager
{
    abstract function getHeaderText();
    abstract function getAppEncoder();
    abstract function getTodoEncoder();
    abstract function getContactEncoder();
    abstract function getFooterText();
}

class BlogManager extends CommunicationManager
{
    public function getHeaderText()
    {
        return "Blog header text";
    }

    // here used template: Factory Method
    public function getAppEncoder()
    {
        return new BlogAppEncoder();
    }

    // here used template: Factory Method
    public function getTodoEncoder()
    {
        return new BlogTodoEncoder();
    }

    // here used template: Factory Method
    public function getContactEncoder()
    {
        return new BlogContactEncoder();
    }

    public function getFooterText()
    {
        return "Blog footer text";
    }
}


//other, helping classes:

abstract class Encoder
{
    abstract function encode();
}

class BlogAppEncoder extends Encoder
{
    public function encode()
    {
        return "BlogApp encoded message \n";
    }
}

class BlogTodoEncoder extends Encoder
{
    public function encode()
    {
        return "BlogTodo encoded message \n";
    }
}

class BlogContactEncoder extends Encoder
{
    public function encode()
    {
        return "BlogContact encoded message \n";
    }
}
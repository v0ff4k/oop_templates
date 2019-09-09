<?php

/**
 * Created by pSom.
 * User: 9r00+
 * at: 09.09.19 - 09:09
 */

//main product of what we manipulating
class RequestHelper{}

//main abstract template for process
abstract class ProcessRequest
{
    abstract function process(RequestHelper $request);
}

//main template for process
class MainProcess extends ProcessRequest
{
    function process(RequestHelper $request)
    {
        print __CLASS__ . ": doing process() in processRequest \n";
    }
}

//main DECORATOR
abstract class DecorateProcess extends ProcessRequest
{
    protected $processRequest;

    public function __construct(ProcessRequest $processRequest)
    {
        $this->processRequest = $processRequest;
    }
}

//extendings
class LogRequest extends DecorateProcess
{
    public function process(RequestHelper $request)
    {
        print __CLASS__ . ": some logging request \n";
        $this->processRequest->process( $request);
    }
}

class AuthRequest extends DecorateProcess
{
    function process(RequestHelper $request)
    {
        print __CLASS__ . ": some  auth-ing  request \n";
        $this->processRequest->process( $request);
    }
}

class StructureRequest extends DecorateProcess
{
    function process(RequestHelper $request)
    {
        print __CLASS__ . ": some structuring  request \n";
        $this->processRequest->process( $request);
    }
}


//client code :::

$process = new AuthRequest( new StructureRequest( (new LogRequest(new MainProcess()))));
$process->process( new RequestHelper());
//OUTPUT:
// Auth..
// Structure..
// Log...
// Main...


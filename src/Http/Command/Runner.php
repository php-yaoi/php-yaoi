<?php

namespace Yaoi\Http\Command;


use Yaoi\Command;
use Yaoi\Command\RunnerContract;
use Yaoi\Http\Response\JsonResponse;
use Yaoi\Io\Request;

class Runner implements RunnerContract
{
    private $response;

    public function __construct(Command $command)
    {
        $this->response = new JsonResponse();
        $command->setResponse($this->response);
    }

    public function run(Request $request = null)
    {

        // TODO: Implement run() method.
    }

}
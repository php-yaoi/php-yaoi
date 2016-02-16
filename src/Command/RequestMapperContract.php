<?php

namespace Yaoi\Command;


use Yaoi\Io\Request;

interface RequestMapperContract
{
    public function __construct(Request $request);

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Exception
     */
    public function readOptions(array $commandOptions);
}
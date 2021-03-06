<?php

namespace Yaoi\Command;


use Yaoi\Io\Request;

interface RequestMapperContract
{
    public function __construct(Request $request);

    /**
     * @param Option[] $commandOptions
     * @param State $commandState
     * @param State $requestState
     * @return mixed
     * @throws Exception
     */
    public function readOptions(array $commandOptions, State $commandState, State $requestState);

    /**
     * @param array $properties
     * @return mixed
     */
    public function makeAnchor(array $properties);

    /**
     * @param Option $option
     * @return string
     */
    public function getExportName(Option $option);


}
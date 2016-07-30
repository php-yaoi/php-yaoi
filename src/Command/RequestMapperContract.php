<?php

namespace Yaoi\Command;


use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Request;

interface RequestMapperContract
{
    public function __construct(Request $request);

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Exception
     */
    public function readOptions(array $commandOptions, \stdClass $commandState, \stdClass $requestState);

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
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
    public function readOptions(array $commandOptions);

    /**
     * @param Option[] $commandOptions
     * @param array $values
     * @return string
     */
    public function makeAnchor(array $properties);

}
<?php

namespace Yaoi\Command;


use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Request;
use Yaoi\String\Expression;

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
     * @param array $properties
     * @return Expression
     */
    public function makeAnchor(array $properties);

    /**
     * @param Option $option
     * @return string
     */
    public function getExportName(Option $option);

}
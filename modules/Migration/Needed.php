<?php

namespace Yaoi\Migration;

interface Needed
{
    /**
     * @return Migration
     */
    public function getMigration();
}
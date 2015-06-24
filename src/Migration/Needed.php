<?php

namespace Yaoi\Migration;

interface Needed
{
    /**
     * @return \Yaoi\Migration
     */
    public function getMigration();
}
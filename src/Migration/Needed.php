<?php

namespace Yaoi\Migration;

interface Needed
{
    /**
     * @return \Yaoi\Migration\Migration
     */
    public function getMigration();
}
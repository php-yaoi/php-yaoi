<?php

use Yaoi\Migration;

interface Required {
    /**
     * @return Migration
     */
    public function getMigration();
}
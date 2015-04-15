<?php

interface Migration_Required {
    /**
     * @return Migration
     */
    public function getMigration();
}
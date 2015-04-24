<?php

interface Migration_Provider {
    /**
     * @return Migration
     */
    public function getMigration();
}
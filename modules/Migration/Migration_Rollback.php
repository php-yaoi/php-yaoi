<?php

interface Migration_Rollback extends Migration {
    /**
     * @return void
     */
    public function rollback();
}
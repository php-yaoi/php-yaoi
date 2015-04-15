<?php

interface Migration_Rollback extends Migration {
    public function rollback();
}
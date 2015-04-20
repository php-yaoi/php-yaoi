<?php

interface Migration_IsApplied extends Migration {
    /**
     * @return bool
     */
    public function isApplied();
}
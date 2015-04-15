<?php

interface Migration {
    /**
     * @return string
     */
    public function getId();
    public function apply();
    public function isApplied();
}
<?php

interface Migration {
    /**
     * @return string
     */
    public function getId();

    /**
     * @return void
     */
    public function apply();
}
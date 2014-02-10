<?php

class Storage_Var extends Storage {
    public function __construct() {
        $this->forceDriver(new Storage_Driver_PhpVar());
    }
} 
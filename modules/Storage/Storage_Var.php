<?php

class Storage_Var extends Storage_Client {
    public function __construct() {
        $this->driver = new Storage_Driver_PhpVar();
    }
} 
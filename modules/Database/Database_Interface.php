<?php

interface Database_Interface extends Mock_Able {
    public function query($statement = null, $binds = null);
    public function log(Log $log = null);
    public function expr($statement, $binds = null);
}
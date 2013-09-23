<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vearutop
 * Date: 18.09.13
 * Time: 22:43
 * To change this template use File | Settings | File Templates.
 */

interface Database_Server_Generic {
    public function __construct(Database_Dsn $dsn);
    public function query($statement);
    public function lastInsertId();
    public function escape($value);
    public function rewind($result);
    public function fetchAssoc($result);
    public function quote($value);
}
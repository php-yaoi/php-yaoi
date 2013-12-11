<?php
abstract class Database_Abstract_Dsn extends String_Dsn {
    public $persistent = false;
    public $reconnect = false;
    public $logQueries = false;
    public $unixSocket;
    public $charset;
}
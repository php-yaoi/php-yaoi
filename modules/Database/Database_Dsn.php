<?php

class Database_Dsn extends Client_Dsn {
    public $persistent = false;
    public $reconnect = false;
    public $logQueries = false;
    public $unixSocket;
    public $charset;
    public $timezone;
}
<?php

namespace Yaoi\Database\Driver;
use Yaoi\Database\Settings;
use Yaoi\String\Quoter;

interface Contract extends Quoter
{
    public function __construct(Settings $dsn);

    public function query($statement);

    public function lastInsertId();

    public function rowsAffected($result);

    public function escape($value);

    public function rewind($result);

    public function fetchAssoc($result);

    public function queryErrorMessage($result);

    public function disconnect();

    public function getDialect();

    /**
     * @return \Yaoi\Database\Utility\Contract
     */
    public function getUtility();
}
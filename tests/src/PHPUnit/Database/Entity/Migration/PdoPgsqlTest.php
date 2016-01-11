<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;


use Yaoi\Database;
use Yaoi\Log;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\User;

class PdoPgsqlTest extends PgsqlTest
{

    public function setUp()
    {
        $this->database = CheckAvailable::getPdoPgsql();
    }


}
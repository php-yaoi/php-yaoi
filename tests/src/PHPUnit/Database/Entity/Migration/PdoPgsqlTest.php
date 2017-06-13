<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;


use YaoiTests\Helper\Database\CheckAvailable;

class PdoPgsqlTest extends PgsqlTest
{

    public function setUp()
    {
        $this->database = CheckAvailable::getPdoPgsql();
    }


}
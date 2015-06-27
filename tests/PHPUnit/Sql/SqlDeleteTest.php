<?php
use Yaoi\Test\PHPUnit\TestCase;



class SqlDeleteTest extends TestCase {
    public function testDelete() {
        $this->assertSame(
            'DELETE FROM table WHERE one = 1',
            (string)\Yaoi\Database::getInstance('test_mysqli')->delete('table')->where('one = ?', 1)
        );
    }
} 
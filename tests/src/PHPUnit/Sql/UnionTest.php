<?php
namespace YaoiTests\PHPUnit\Sql;

use Yaoi;
use Yaoi\Sql\Symbol;

class UnionTest extends \YaoiTests\PHPUnit\Sql\TestBase
{
    public function testUnion()
    {
        $result = (string)$this->db->select('table1')->where('one = ?', 1)->groupBy('field')
            ->union("SELECT * FROM ?", new Symbol('table2'))
            ->unionAll(Yaoi\Database::getInstance('test_mysqli')->select('tt')->where('filtered = ?', 1));

        $this->assertSame('SELECT * FROM table1 WHERE one = 1 GROUP BY field UNION SELECT * FROM `table2` UNION ALL SELECT * FROM tt WHERE filtered = 1', $result);
    }


    public function testUnion2()
    {
        $expr = $this->db->select();
        $first = true;
        $ids = array(1, 2, 3, 4);
        foreach ($ids as $id) {
            if ($first) {
                $expr->select('? AS id', $id);
                $first = false;
            }
            else {
                $expr->unionAll('SELECT ?', $id);
            }
        }

        $this->assertSame('SELECT 1 AS id UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4', (string)$expr);
    }


    public function testUnion3()
    {
        $expr = $this->db->select();
        $first = true;
        $ids = array(1, 2);
        foreach ($ids as $id) {
            if ($first) {
                $expr->select('? AS id', $id);
                $first = false;
            } else {
                $expr->unionAll('SELECT ?', $id);
            }
        }

        $this->assertSame('SELECT 1 AS id UNION ALL SELECT 2', (string)$expr);
    }

}
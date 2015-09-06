<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/25/15
 * Time: 15:19
 */

namespace Yaoi\Migration;


use Yaoi\Log;

abstract class AbstractMigration implements Migration
{
    protected $dryRun = true;
    /** @var  Log */
    protected $log;

    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function setLog(Log $log = null)
    {
        $this->log = $log;
    }

    public function setDryRun($yes = true)
    {
        $this->dryRun = $yes;
    }

}
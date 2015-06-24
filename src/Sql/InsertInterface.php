<?php

namespace Yaoi\Sql;

interface InsertInterface extends StatementInterface
{
    /**
     * @param $array
     * @return $this
     */
    public function valuesRow($array);

    /**
     * @param $collection
     * @return $this
     */
    public function valuesRows($collection);

}
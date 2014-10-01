<?php

interface Sql_InsertInterface extends Sql_StatementInterface
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
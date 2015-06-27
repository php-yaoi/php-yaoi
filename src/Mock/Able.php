<?php
namespace Yaoi\Mock;

use Yaoi\Mock;

interface Able
{
    /**
     * @param Mock $dataSet
     * @return $this
     */
    public function mock(Mock $dataSet = null);
}
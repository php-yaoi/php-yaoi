<?php

class View_HighCharts_Series extends Base_Class {
    const TYPE_AREA = 'area';
    const TYPE_AREA_SPLINE = 'areaspline';
    const TYPE_BAR = 'bar';
    const TYPE_COLUMN = 'column';
    const TYPE_LINE = 'line';
    const TYPE_PIE = 'pie';
    const TYPE_SCATTER = 'scatter';
    const TYPE_SPLINE = 'spline';

    const TYPE_AREA_RANGE = 'arearange';
    const TYPE_AREA_SPLINE_RANGE = 'areasplinerange';
    const TYPE_COLUMN_RANGE = 'columnrange';

    private $data = array();
    private $id;
    private $index;
    private $legendIndex;
    private $name;
    private $type;
    private $xAxis;
    private $yAxis;
    private $zIndex;

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setData(&$rows) {
        $this->data = $rows;
        return $this;
    }

    public function addRow($x, $y) {
        $this->data []= array(1 * $x, 1 * $y);
        return $this;
    }


    const VALUE_LOW = 1;
    const VALUE_HIGH = 2;
    public function addRangeRow($x, $y, $i = self::VALUE_LOW) {
        if (!isset($this->data[$x])) {
            $this->data[$x] = array(1 * $x, null, null);
        }
        $this->data [$x][$i] = 1 * $y;
    }

    public function setZIndex($zIndex) {
        $this->zIndex = $zIndex;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type = self::TYPE_LINE) {
        $this->type = $type;
        return $this;
    }


    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        if (null === $this->name) {
            $this->setName($id);
        }
        return $this;
    }


    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @param mixed $legendIndex
     * @return $this
     */
    public function setLegendIndex($legendIndex)
    {
        $this->legendIndex = $legendIndex;
        return $this;
    }

    /**
     * @param mixed $xAxis
     * @return $this
     */
    public function setXAxis($xAxis)
    {
        $this->xAxis = $xAxis;
        return $this;
    }

    /**
     * @param mixed $yAxis
     * @return $this
     */
    public function setYAxis($yAxis)
    {
        $this->yAxis = $yAxis;
        return $this;
    }

    public function exportOptions() {
        $result = get_object_vars($this);
        $result['data'] = array_values($result['data']);
        foreach ($result as $k => $v) {
            if (null === $v) {
                unset($result[$k]);
            }
        }
        return $result;
    }
}
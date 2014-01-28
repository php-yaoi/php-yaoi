<?php

class View_HighCharts_Series extends Base_Class {
    const TYPE_AREA_RANGE = 'arearange';
    const TYPE_LINE = 'line';

    private $name;
    private $data;
    private $zIndex;
    private $type;

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setData(&$rows) {
        $this->data = $rows;
        return $this;
    }

    public function setZIndex($zIndex) {
        $this->zIndex = $zIndex;
        return $this;
    }

    public function setType($type = self::TYPE_LINE) {
        $this->type = $type;
        return $this;
    }

    public function exportOptions() {
        return get_object_vars($this);
    }
}
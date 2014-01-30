<?php

class View_HighCharts_Table extends View_Table_Renderer {
    const DATA_TYPE_REGULAR = 'regular';
    const DATA_TYPE_NAMED = 'named';

    public $dataType = self::DATA_TYPE_REGULAR;
    protected $tag = 'div';
    protected $content = '';
    private $options;

    /**
     * @var View_HighCharts
     */
    private $highCharts;

    public function __construct(&$rows = null) {
        $this->highCharts = new View_HighCharts($rows);
    }

    static $uniqueId = 0;

    protected function renderHead() {
        if (null === $this->id) {
            $this->id = 'hc-container-' . ++self::$uniqueId;
        }
        parent::renderHead();
    }

    public function withRegularSeries() {
        $this->dataType = self::DATA_TYPE_REGULAR;
        return $this;
    }

    public function withNamedSeries() {
        $this->dataType = self::DATA_TYPE_NAMED;
        return $this;
    }

    private function seriesFillRegular() {
        $keys = array();
        $xAxis = false;
        foreach ($this->rows as $row) {
            if (!$keys) {
                $keys = array_keys($row);
                $xAxis = array_shift($keys);
            }

            foreach ($keys as $key) {
                $this->highCharts->addRow($row[$xAxis], $row[$key], $key);
            }
        }
    }

    private function seriesFillNamed() {
        $keys = array();
        $xAxis = false;
        $name = '';
        $value = '';
        foreach ($this->rows as $row) {
            if (!$keys) {
                $keys = array_keys($row);
                $xAxis = $keys[0];
                $value = $keys[1];
                $name = $keys[2];
            }

            $this->highCharts->addRow($row[$xAxis], $row[$value], $row[$name]);
        }
    }




    protected function renderTail() {
        parent::renderTail();
        $this->highCharts->render();
    }

}
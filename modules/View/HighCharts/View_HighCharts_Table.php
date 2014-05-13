<?php

class View_HighCharts_Table extends View_Table_Renderer {
    const DATA_TYPE_REGULAR = 'regular';
    const DATA_TYPE_NAMED = 'named';

    public $dataType = self::DATA_TYPE_REGULAR;
    protected $tag = 'div';
    protected $content = '';

    /**
     * @var View_HighCharts
     */
    private $highCharts;

    public function __construct(&$rows = null) {
        if (null !== $rows) {
            $this->setRows($rows);
        }

        $this->highCharts = new View_HighCharts();
    }

    static $uniqueId = 0;

    protected function renderHead() {
        if (null === $this->id) {
            $this->id = 'hc-container-' . ++self::$uniqueId;
        }
        $this->highCharts->setContainerSelector('#' . $this->id);
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
        foreach ($this->rows as $row) {

            $xValue = array_shift($row);
            foreach ($row as $key => $value) {
                $this->highCharts->addRow($xValue, $value, $key);
            }
        }
    }

    public function withChartDo(Closure $closure) {
        $closure($this->highCharts);
        return $this;
    }


    /**
     * @return View_HighCharts
     */
    public function getHighCharts() {
        return $this->highCharts;
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

        switch ($this->dataType) {
            case self::DATA_TYPE_REGULAR: $this->seriesFillRegular();break;
            case self::DATA_TYPE_NAMED: $this->seriesFillNamed();break;
            default: throw new View_Exception('Wrong data type', View_Exception::WRONG_DATA_TYPE);
        }

        $this->highCharts->render();
    }

}
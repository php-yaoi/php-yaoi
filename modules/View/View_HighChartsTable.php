<?php

class View_HighChartsTable extends View_Table_Renderer {
    const DATA_TYPE_REGULAR = 'regular';
    const DATA_TYPE_NAMED = 'named';

    public $dataType = self::DATA_TYPE_REGULAR;
    protected $tag = 'div';
    protected $content = '';

    public function __construct(&$rows = null, $dataType = self::DATA_TYPE_REGULAR) {
        if (null !== $rows) {
            $this->setRows($rows);
        }

        $this->dataType = $dataType;

        $this->options = array(
            'title' => false,

            'chart' => array(
                'renderTo' => $this->id,
                'zoomType' => 'y',
                'resetZoomButton' => array(
                    'position' => array(
                        'align' => 'left', // by default
                        'verticalAlign' => 'bottom', // by default
                        'x' => 0,
                        'y' => -130,
                    )
                )
            ),

            'legend' => array(
                'enabled' => true,
                //'layout' => 'vertical',
                'verticalAlign' => 'top'
            ),

            'plotOptions' => array(
                'series' => array(
                    'marker' => array(
                        'enabled' => false
                    )
                )
            ),

            'tooltip' => array(
                'crosshairs' => array(true, true),
                'shared' => false,
            ),

            'credits' => array(
                'enabled' => false
            )

        );


    }

    protected $rows = array();
    static $uniqueId = 0;

    private $series = array();



    protected function renderHead() {
        $this->id = 'hc-container-' . ++self::$uniqueId;
        parent::renderHead();
    }

    public function isRegular() {
        $this->dataType = self::DATA_TYPE_REGULAR;
        return $this;
    }

    public function isNamed() {
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

                foreach ($keys as $key) {
                    $this->series [$key]= array(
                        'name' => $key,
                        'type' => 'spline',
                        'data' => array()
                    );
                }
            }

            foreach ($keys as $key) {
                $this->series[$key]['data'] []= array(1 * $row[$xAxis], 1 * $row[$key]);
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
                $xAxis = array_shift($keys);
                $name = array_shift($keys);
                $value = array_shift($keys);
            }

            if (!isset($series[$row['name']])) {
                $this->series[$row['name']] = array(
                    'name' => $row[$name],
                    'type' => 'spline',
                    'data' => array()
                );
            }

            $this->series[$row[$name]]['data'] []= array(1 * $row[$xAxis], 1 * $row[$value]);
        }
    }


    private static function &arrayMergeRecursiveDistinct(array &$array1, &$array2 = null)
    {
        $merged = $array1;

        if (is_array($array2))
            foreach ($array2 as $key => $val)
                if (is_array($array2[$key]))
                    $merged[$key] = is_array($merged[$key]) ? self::arrayMergeRecursiveDistinct($merged[$key], $array2[$key]) : $array2[$key];
                else
                    $merged[$key] = $val;

        return $merged;
    }


    public function addOptions($options) {
        $this->options = self::arrayMergeRecursiveDistinct($this->options, $options);
        return $this;
    }


    protected function renderData() {

        switch ($this->dataType) {
            case self::DATA_TYPE_REGULAR: $this->seriesFillRegular();break;
            case self::DATA_TYPE_NAMED: $this->seriesFillNamed();break;
            default: throw new View_Exception('Wrong data type', View_Exception::WRONG_DATA_TYPE);
        }

        $this->options['series'] = array_values($this->series);

        ?>
<script src="http://code.highcharts.com/stock/highstock.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script>
(function(){
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

    $('#<?=$this->id?>').highcharts(<?= json_encode($this->options) ?>);
})();
</script><?php
    }

    protected function renderTail() {
        parent::renderTail();
        $this->renderData();
    }

}
<?php

class View_HighCharts extends Base_Class implements View_Renderer{
    public function isEmpty()
    {
        // TODO: Implement isEmpty() method.
    }

    public function render()
    {
        // TODO: Implement render() method.
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    private $options;
    private $series = array();


    public function __construct($id) {
        $this->options = array(
            'title' => false,

            'chart' => array(
                'renderTo' => $id,
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



    public function setTitle($title) {
        if ($title) {
            $this->options['title']['text'] = $title;
        }
        else {
            $this->options['title'] = false;
        }
        return $this;
    }

    public function setYTitle($title) {
        if ($title) {
            $this->options['yAxis']['title']['text'] = $title;
        }
        else {
            $this->options['yAxis']['title']['text'] = null;
        }
    }


    public function withDateAxis() {
        $this->options['xAxis']['type'] = 'datetime';
        return $this;
    }


    public function addOptions($options) {
        $this->options = Utils::arrayMergeRecursiveDistinct($this->options, $options);
        return $this;
    }

    /**
     * @var View_HighCharts_Series
     */
    private $defaultSeries;

    /**
     * @param mixed $defaultSeries
     * @return $this
     */
    public function setDefaultSeries(View_HighCharts_Series $defaultSeries = null) {
        $this->defaultSeries = $defaultSeries;
        return $this;
    }


    public function addRow($x, $y, $id = 'default') {
        if (!$series = &$this->series[$id]) {
            if ($this->defaultSeries) {
                $series = clone $this->defaultSeries;
            }
            else {
                $series = new View_HighCharts_Series();
            }
            $series->setId($id);
        }
        $series->addRow($x, $y);
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

                $('#<?php echo $this->id?>').highcharts(<?php echo json_encode($this->options) ?>);
            })();
        </script><?php
    }



}
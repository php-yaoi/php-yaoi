<?php


class View_HighCharts extends Base_Class implements View_Renderer{
    public function isEmpty()
    {
        return empty($this->series);
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    private $options;
    /**
     * @var View_HighCharts_Series[]
     */
    private $series = array();


    public function __construct() {
        $this->options = array(
            'title' => false,

            /*
            'chart' => array(
                'resetZoomButton' => array(
                    'position' => array(
                        'align' => 'left', // by default
                        'verticalAlign' => 'bottom', // by default
                        'x' => 0,
                        'y' => -130,
                    )
                )
            ),
            */

            'chart' => array(
                'zoomType' => 'x'
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



    public function setType($type = View_HighCharts_Series::TYPE_LINE) {
        $this->options['chart']['type'] = $type;
        return $this;
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
        return $this;
    }

    public function setXTitle($title) {
        if ($title) {
            $this->options['xAxis']['title']['text'] = $title;
        }
        else {
            $this->options['xAxis']['title']['text'] = null;
        }
        return $this;
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
        //echo 'row added';
        if (!$series = &$this->series[$id]) {
            if ($this->defaultSeries) {
                $series = clone $this->defaultSeries;
            }
            else {
                $series = new View_HighCharts_Series();
            }
            $series->setId($id);
        }
        if (isset($this->ranges[$id])) {
            $series->addRangeRow($x, $y, $this->ranges[$id]);
        }
        else {
            $series->addRow($x, $y);
        }
        return $this;
    }

    private $ranges = array();
    public function addSeries(View_HighCharts_Series $series, $rangeHighId = null) {
        $this->series[$rangeLowId = $series->getId()] = $series;
        if (null !== $rangeHighId) {
            $this->series[$rangeHighId] = $series;
            $this->ranges[$rangeLowId] = View_HighCharts_Series::VALUE_LOW;
            $this->ranges[$rangeHighId] = View_HighCharts_Series::VALUE_HIGH;
        }
        return $this;
    }


    private $renderToSelector;
    public function renderToSelector($id) {
        $this->renderToSelector = $id;
        return $this;
    }




    public function render() {
        $this->options['series'] = array();
        //var_dump($this->series);
        foreach ($this->series as $id => $series) {
            if (isset($this->ranges[$id]) && View_HighCharts_Series::VALUE_HIGH == $this->ranges[$id]) {
                continue;
            }
            $this->options['series'] []= $series->exportOptions();
        }

        ?>
<script type="text/javascript" src="http://code.highcharts.com/stock/highstock.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/highcharts-more.js"></script>
<script type="text/javascript">
(function(){
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

    $('<?php echo $this->renderToSelector ?>').highcharts(<?php echo json_encode($this->options) ?>);
})();
</script><?php
    }



}
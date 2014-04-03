<?php
//rename View_HighChartsTable to View_HighCharts_Table
/*
class View_HighChartsTable {}
//*/

// move View_HighCharts_Table->addOptions() to View_HighCharts_Table->getHighCharts()->addOptions()
// ->withChartDo(function(View_HighCharts $chart)use($options){$chart->addOptions($options);})
/*
class View_HighCharts_Table {
    public static function addOptions(){}
}
//*/


// rename Http_ClientException to Http_Client_Exception
/*
class Http_ClientException {}
//*/


// 2014.02.10 rename Storage_Client to Storage
/*
class Storage_Client {}
//*/


// 2014.02.10 rename Storage_Conf::$dsn to Storage::$conf
/*
class Storage_Conf {}
//*/


// 2014.03.03 remove Client::createByDsn in favour of __construct or ::create()
// remove Client::createByConfId in favour of ::getInstance()
/*
class Client {
    public static function createByDsn(){}
    public static function createByConfId() {}
}
//*/


// 2014.03.04
// rename Database_Client to Database
/*
class Database_Client {}
//*/



// rename Abstact_App to Yaoi
/*
class Abstract_App {}
//*/



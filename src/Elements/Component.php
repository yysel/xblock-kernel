<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 19-10-6
 * Time: 上午5:58
 */

namespace XBlock\Kernel\Elements;


use XBlock\Kernel\Elements\Components\Chart;
use XBlock\Kernel\Elements\Components\Detail;
use XBlock\Kernel\Elements\Components\LineChart;
use XBlock\Kernel\Elements\Components\Table;

class Component
{

    static public function table(): Table
    {
        return new Table;
    }


    static public function detail(): Detail
    {
        return new Detail;
    }

    static public function chart(): Chart
    {
        return new Chart;
    }

    static public function lineChart(): LineChart
    {
        return new LineChart();
    }

    static public function pieChart(): Chart
    {
        $chart = new Chart;
        return $chart->pie();
    }

    static public function ringChart(): Chart
    {
        $chart = new Chart;
        return $chart->ring();
    }

    static public function barChart(): Chart
    {
        $chart = new Chart;
        return $chart->bar();
    }


}
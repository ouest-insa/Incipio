<?php

namespace Mgate\StatBundle\Manager;

use Mgate\StatBundle\Controller\IndicateursController;
use Ob\HighchartsBundle\Highcharts\Highchart;

class ChartFactory
{
    public function newColumnChart($series, $categories)
    {
        $ob = new Highchart();

        $ob->chart->type('column');
        $ob->yAxis->min(0);
        $ob->yAxis->max(100);
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);

        $ob->series($series);
        $ob->xAxis->categories($categories);

        return $ob;
    }

    public function newPieChart($series)
    {
        $ob = new Highchart();

        $ob->plotOptions->pie(['allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => ['enabled' => false]]);
        $ob->series($series);
        $ob->credits->enabled(false);

        return $ob;
    }

    public function newLineChart($series)
    {
        $ob = new Highchart();

        $ob->credits->enabled(false);
        $ob->legend->align('right');
        $ob->legend->backgroundColor('#F6F6F6');
        $ob->legend->enabled(true);
        $ob->legend->floating(false);
        $ob->legend->layout('vertical');
        $ob->legend->reversed(true);
        $ob->legend->verticalAlign('middle');

        $ob->series($series);

        return $ob;
    }

    public function newColumnDrilldownChart($series, $drilldownSeries)
    {
        $ob = new Highchart();
        $ob->chart->type('column');
        $ob->drilldown->series($drilldownSeries);
        $ob->series($series);

        $ob->xAxis->type('category');
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);

        return $ob;
    }
}

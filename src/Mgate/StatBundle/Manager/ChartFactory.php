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
        $style = IndicateursController::DEFAULT_STYLE;
        $ob->title->style(['fontWeight' => 'bold', 'fontSize' => '20px']);
        $ob->xAxis->labels(['style' => $style]);
        $ob->yAxis->labels(['style' => $style]);
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);

        $ob->series($series);
        $ob->xAxis->categories($categories);

        $ob->title->text('Title');
        $ob->yAxis->title(['text' => 'Title y', 'style' => $style]);
        $ob->xAxis->title(['text' => 'Title x', 'style' => $style]);

        return $ob;
    }

    public function newPieChart($series)
    {
        $ob = new Highchart();

        $ob->plotOptions->pie(['allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => ['enabled' => false]]);
        $ob->series($series);
        $ob->title->style(['fontWeight' => 'bold', 'fontSize' => '20px']);
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
        $ob->title->style(['fontWeight' => 'bold', 'fontSize' => '20px']);

        $ob->series($series);

        return $ob;
    }
}

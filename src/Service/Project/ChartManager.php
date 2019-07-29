<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Project;

use App\Entity\Project\ClientContact;
use App\Entity\Project\Etude;
use App\Entity\Project\Phase;
use App\Entity\Publish\Document;
use Doctrine\Common\Persistence\ObjectManager;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Zend\Json\Expr;

class ChartManager /*extends \Twig_Extension*/
{
    protected $em;

    protected $etudeManager;

    protected $logger;

    protected $namingConvention;

    protected $projectDir;

    private const SIX_MONTHS = 15724800;

    public function __construct(ObjectManager $em, EtudeManager $etudeManager, LoggerInterface $logger,
                                KeyValueStore $keyValueStore, KernelInterface $kernel)
    {
        $this->em = $em;
        $this->etudeManager = $etudeManager;
        $this->logger = $logger;
        if ($keyValueStore->exists('namingConvention')) {
            $this->namingConvention = $keyValueStore->get('namingConvention');
        } else {
            $this->namingConvention = 'id';
        }
        $this->projectDir = $kernel->getProjectDir();
    }

    public function getGantt(Etude $etude, $type)
    {
        // Chart
        $series = [];
        $data = [];
        $cats = [];
        $naissance = new \DateTime(); // first date on the chart
        $mort = $etude->getDateCreation(); // last date on the chart

        //Contacts Client
        if (0 != $etude->getClientContacts()->count() && 'suivi' == $type) {
            /** @var ClientContact $contact */
            foreach ($etude->getClientContacts() as $contact) {
                $date = $contact->getDate();
                if ($naissance >= $date) {
                    $naissance = clone $date;
                }
                if ($mort <= $date) {
                    $mort = clone $date;
                }

                $data[] = ['x' => count($cats), 'y' => $date->getTimestamp() * 1000,
                           'titre' => $contact->getObjet(), 'detail' => 'fait par ' . $contact->getFaitPar()
                            ->getPrenomNom() . ' le ' . $date->format('d/m/Y'),
                ];
            }
            $series[] = ['type' => 'scatter', 'data' => $data];
            $cats[] = 'Contact client';
        }

        //Documents
        if ('suivi' == $type) {
            $data = [];
            $count_cats = count($cats);
            for ($j = 0; $j < $count_cats; ++$j) {
                $data[] = [];
            }
            $dataSauv = $data;

            if ($etude->getAp() && $etude->getAp()->getDateSignature()) {
                $date = $etude->getAp()->getDateSignature();
                if ($naissance >= $date) {
                    $naissance = clone $date;
                }
                if ($mort <= $date) {
                    $mort = clone $date;
                }

                $data[] = ['x' => count($cats), 'y' => $date->getTimestamp() * 1000,
                           'titre' => 'Avant-Projet', 'detail' => 'signé le ' . $date->format('d/m/Y'),
                ];
                $series[] = ['type' => 'scatter', 'data' => $data,
                             'marker' => ['symbol' => 'square', 'fillColor' => 'blue'],
                ];
            }
            $data = $dataSauv;
            if ($etude->getCc() && $etude->getCc()->getDateSignature()) {
                $date = $etude->getCc()->getDateSignature();
                if ($naissance >= $date) {
                    $naissance = clone $date;
                }
                if ($mort <= $date) {
                    $mort = clone $date;
                }

                $data[] = ['x' => count($cats), 'y' => $date->getTimestamp() * 1000,
                           'titre' => 'Convention Client', 'detail' => 'signé le ' . $date->format('d/m/Y'),
                ];
                $series[] = ['type' => 'scatter', 'data' => $data,
                             'marker' => ['symbol' => 'triangle', 'fillColor' => 'red'],
                ];
            }
            $data = $dataSauv;
            if ($etude->getPvr() && $etude->getPvr()->getDateSignature()) {
                $date = $etude->getPvr()->getDateSignature();
                if ($naissance >= $date) {
                    $naissance = clone $date;
                }
                if ($mort <= $date) {
                    $mort = clone $date;
                }

                $data[] = ['x' => count($cats), 'y' => $date->getTimestamp() * 1000,
                           'titre' => 'Procès Verbal de Recette', 'detail' => 'signé le ' . $date->format('d/m/Y'),
                ];
                $series[] = ['type' => 'scatter', 'data' => $data, 'marker' => ['symbol' => 'circle']];
            }
            $cats[] = 'Documents';
        }

        //Etude
        if ('suivi' == $type) {
            $data = [];
            $count_cats = count($cats);
            for ($j = 0; $j < $count_cats; ++$j) {
                $data[] = [];
            }

            if ($etude->getDateLancement() && $etude->getDateFin(true)) {
                $debut = $etude->getDateLancement();
                $fin = $etude->getDateFin(true);

                $data[] = ['low' => $debut->getTimestamp() * 1000, 'y' => $fin->getTimestamp() * 1000,
                           'color' => '#005CA4',
                           'titre' => 'Durée de déroulement des phases',
                           'detail' => 'du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y'),
                ];

                $cats[] = 'Etude';
            }
        }

        /** @var Phase $phase */
        foreach ($etude->getPhases() as $phase) {
            if ($phase->getDateDebut() && $phase->getDelai()) {
                $debut = $phase->getDateDebut();
                if ($naissance >= $debut) {
                    $naissance = clone $debut;
                }
                /** @var \DateTime $fin */
                $fin = $phase->getDateFin();
                if ($mort <= $fin) {
                    $mort = clone $fin;
                }

                $func = new Expr('function() {return this.point.titre;}');
                $data[] = ['low' => $fin->getTimestamp() * 1000, 'y' => $debut->getTimestamp() * 1000,
                    'titre' => $phase->getTitre(),
                    'detail' => 'du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y'),
                    'color' => '#F26729',
                    'dataLabels' => [
                        'enabled' => true,
                        'align' => 'left',
                        'inside' => true,
                        'verticalAlign' => 'bottom',
                        'formatter' => $func,
                        'y' => -5,
                        'style' => [
                            'color' => '#000000',
                            'fontSize' => '11px',
                            'fontFamily' => 'Liberation Sans',
                            'textShadow' => '0 0 0px white', ],
                        ],
                    ];
            } else {
                $data[] = [];
            }

            $cats[] = 'Phase n°' . ($phase->getPosition() + 1);
        }
        $series[] = ['type' => 'bar', 'data' => $data];

        //Today, à faire à la fin
        $data = [];
        if ('suivi' == $type) {
            if ($mort->getTimestamp() + self::SIX_MONTHS > time()) {
                $now = new \DateTime('NOW');
                $mort = ($now > $mort ? clone $now : $mort);
                $data[] = ['x' => 0, 'y' => $now->getTimestamp() * 1000,
                           'titre' => "aujourd'hui", 'detail' => 'le ' . $now->format('d/m/Y'),
                ];
                $data[] = ['x' => count($cats) - 1, 'y' => $now->getTimestamp() * 1000,
                           'titre' => "aujourd'hui", 'detail' => 'le ' . $now->format('d/m/Y'),
                ];
                $series[] = ['type' => 'spline', 'data' => $data, 'marker' => ['radius' => 1, 'color' => '#545454'],
                             'color' => '#545454', 'lineWidth' => 1, 'pointWidth' => 5,
                ];
            }
        }

        $ob = $this->ganttChartFactory($series, $cats);
        $ob->yAxis->min($naissance->sub(new \DateInterval('P1D'))->getTimestamp() * 1000);
        $ob->yAxis->max($mort->add(new \DateInterval('P1D'))->getTimestamp() * 1000);

        return $ob;
    }

    public function exportGantt(Highchart $ob, $filename, $width = 800)
    {
        $logger = $this->logger;

        // Create the file
        $chemin = $this->projectDir . '' . Document::DOCUMENT_TMP_FOLDER . '/' . $filename . '.json';
        $destination = $this->projectDir . '' . Document::DOCUMENT_TMP_FOLDER . '/' . $filename . '.png';

        $render = $ob->render();

        // On garde que ce qui est intéressant
        $render = strstr($render, '{', false);
        $render = substr($render, 1);
        $render = strstr($render, '{', false);

        $render = substr($render, 0, strrpos($render, '}')); // on tronque jusqu'a la dernire ,
        $render = substr($render, 0, strrpos($render, '}')); // on tronque jusqu'a la dernire ,
        $render .= '}';

        $fp = fopen($chemin, 'w');
        if ($fp) {
            if (false === fwrite($fp, $render)) {
                $logger->err("exportGantt: impossible d'écrire dans le fichier .json (" . $chemin . ')');

                return false;
            }

            fclose($fp);
        } else {
            $logger->err('exportGantt: impossible de créer le fichier .json (' . $chemin . ')');

            return false;
        }

        $cmd = 'phantomjs ' . $this->projectDir . '/public/js/highcharts-convert.js -infile ' . $chemin . ' -outfile ' . $destination . ' -width ' . $width . ' -constr Chart';
        $output = shell_exec($cmd);
        //l'execution de la commande affiche des messages de fonctionnement. On ne retient que la 3eme ligne (celle de la destination quand tout fonctionne bien).
        //Highcharts.options.parsed Highcharts.customCode.parsed tmp/gantt411ENS.png
        $temp = preg_split('#\n#', $output);
        $output = $temp[2];
        if (0 == strncmp($output, $destination, strlen($destination))) {
            if (file_exists($destination)) {
                return true;
            } else {
                $logger->err("exportGantt: le fichier final n'existe pas (" . $destination . ')');

                return false;
            }
        } else {
            $logger->err("exportGantt: erreur lors de la génération de l'image: " . $output, ['cmd' => $cmd]);

            return false;
        }
    }

    public function getGanttSuivi(array $etudes)
    {
        // Chart
        $series = [];
        $data = [];
        $categories = [];
        $naissance = new \DateTime();
        $mort = new \DateTime();

        //Etudes
        /** @var Etude $etude */
        foreach ($etudes as $etude) {
            if ($etude->getDateLancement() && $etude->getDateFin()) {
                $debut = $etude->getDateLancement();
                $fin = $etude->getDateFin();

                if ($naissance >= $debut) {
                    $naissance = clone $debut;
                }
                if ($mort <= $fin) {
                    $mort = clone $fin;
                }

                $func = new Expr('function() {return this.point.titre;}');
                $data[] = ['low' => $fin->getTimestamp() * 1000, 'y' => $debut->getTimestamp() * 1000,
                    'titre' => $etude->getNom(),
                    'detail' => 'du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y'),
                    'color' => '#F26729',
                    'dataLabels' => [
                        'enabled' => true,
                        'align' => 'left',
                        'inside' => true,
                        'verticalAlign' => 'bottom',
                        'formatter' => $func,
                        'y' => -5,
                        'style' => [
                            'color' => '#000000',
                            'fontSize' => '11px',
                            'fontFamily' => 'Liberation Sans',
                            'textShadow' => '0 0 0px white', ],
                        ],
                    ];
            } else {
                $data[] = [];
            }

            $categories[] = $etude->getReference($this->namingConvention);
        }
        $series[] = ['type' => 'bar', 'data' => $data];

        //Today, à faire à la fin
        $data = [];

        $now = new \DateTime('NOW');
        $data[] = ['x' => 0, 'y' => $now->getTimestamp() * 1000,
                   'titre' => "aujourd'hui", 'detail' => 'le ' . $now->format('d/m/Y'),
        ];
        $data[] = ['x' => count($categories) - 1, 'y' => $now->getTimestamp() * 1000,
                   'titre' => "aujourd'hui", 'detail' => 'le ' . $now->format('d/m/Y'),
        ];
        $series[] = ['type' => 'spline', 'data' => $data, 'marker' => ['radius' => 1, 'color' => '#545454'],
                     'color' => '#545454', 'lineWidth' => 1, 'pointWidth' => 5,
        ];

        $ob = $this->ganttChartFactory($series, $categories);
        $ob->chart->renderTo('ganttChart');  // The #id of the div where to render the chart
        $ob->yAxis->min($naissance->sub(new \DateInterval('P1D'))->getTimestamp() * 1000);
        $ob->yAxis->max($mort->add(new \DateInterval('P1D'))->getTimestamp() * 1000);

        return $ob;
    }

    private function ganttChartFactory($series, $categories)
    {
        $style = ['color' => '#000000', 'fontSize' => '11px', 'fontFamily' => 'Calibri (Corps)'];
        $ob = new Highchart();
        $ob->chart->renderTo('ganttChart');  // The #id of the div where to render the chart
        $ob->title->text('');
        $ob->xAxis->title(['text' => '']);
        $ob->xAxis->categories($categories);
        $ob->xAxis->labels(['style' => $style]);
        $ob->yAxis->title(['text' => '']);
        $ob->yAxis->type('datetime');

        $ob->yAxis->labels(['style' => $style]);
        $ob->chart->zoomType('y');
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);
        $ob->exporting->enabled(false);
        $ob->plotOptions->series(['pointPadding' => 0, 'groupPadding' => 0, 'pointWidth' => 10,
                                  'marker' => ['radius' => 5],
                                  'tooltip' => ['pointFormat' => '<b>{point.titre}</b><br /> {point.detail}'],
        ]);
        $ob->plotOptions->scatter(['tooltip' => ['headerFormat' => '']]);
        $ob->series($series);

        return $ob;
    }
}

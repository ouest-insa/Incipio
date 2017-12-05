<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\StatBundle\Controller;

use Mgate\StatBundle\Entity\Indicateur;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndicateursController extends Controller
{
    const STATE_ID_EN_COURS_X = 2;

    const STATE_ID_TERMINEE_X = 4;

    const DEFAULT_STYLE = ['color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px'];

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $indicateurs = $em->getRepository('MgateStatBundle:Indicateur')->findAll();
        $statsBrutes = ['Pas de données' => 'A venir'];

        return $this->render('MgateStatBundle:Indicateurs:index.html.twig', ['indicateurs' => $indicateurs,
            'stats' => $statsBrutes,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function debugAction($get)
    {
        $indicateur = new Indicateur();
        $indicateur->setTitre($get)
            ->setMethode($get);

        return $this->render('MgateStatBundle:Indicateurs:debug.html.twig', ['indicateur' => $indicateur,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function ajaxAction(Request $request)
    {
        if ('GET' == $request->getMethod()) {
            $chartMethode = $request->query->get('chartMethode');
            $em = $this->getDoctrine()->getManager();
            $indicateur = $em->getRepository('MgateStatBundle:Indicateur')->findOneByMethode($chartMethode);

            if (null !== $indicateur) {
                $method = $indicateur->getMethode();

                return $this->$method(); //okay, it's a little bit dirty ...
            }
        }

        return new Response('<!-- Chart ' . $chartMethode . ' does not exist. -->');
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Retard par mandat (gestion d'études)
     * Basé sur les dates de signature CC et non pas les numéros
     */
    private function getRetardParMandat()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy([], ['dateSignature' => 'asc']);

        /* Initialisation */
        $nombreJoursParMandat = [];
        $nombreJoursAvecAvenantParMandat = [];

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreJoursParMandat[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreJoursAvecAvenantParMandat[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = self::STATE_ID_EN_COURS_X == $etude->getStateID()
                || self::STATE_ID_TERMINEE_X == $etude->getStateID();

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);
                if ($etude->getDelai()) {
                    $nombreJoursParMandat[$idMandat] += $etude->getDelai(false)->days;
                    $nombreJoursAvecAvenantParMandat[$idMandat] += $etude->getDelai(true)->days;
                }
            }
        }

        $data = [];
        $categories = [];
        foreach ($nombreJoursParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $etudeManager->mandatToString($idMandat);
                $data[] = ['y' => 100 * ($nombreJoursAvecAvenantParMandat[$idMandat] - $datas) / $datas, 'nombreEtudes' => $datas, 'nombreEtudesAvecAv' => $nombreJoursAvecAvenantParMandat[$idMandat] - $datas];
            }
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => 'Nombre de jours de retard / nombre de jours travaillés', 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Retard par Mandat');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br/>');
        $ob->tooltip->pointFormat('Les études ont duré en moyenne {point.y:.2f} % de plus que prévu<br/>avec {point.nombreEtudesAvecAv} jours de retard sur {point.nombreEtudes} jours travaillés');
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->yAxis->max(100);
        $ob->yAxis->title(['text' => 'Taux (%)', 'style' => self::DEFAULT_STYLE]);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombre d'études par mandat (gestion d'études)
     * Basé sur les dates de signature CC et non pas les numéros
     */
    private function getNombreEtudes()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy([], ['dateSignature' => 'asc']);

        /* Initialisation */
        $nombreEtudesParMandat = [];

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesParMandat[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = self::STATE_ID_EN_COURS_X == $etude->getStateID()
                || self::STATE_ID_TERMINEE_X == $etude->getStateID();
            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);
                $nombreEtudesParMandat[$idMandat] += 1;
            }
        }
        $data = [];
        $categories = [];
        foreach ($nombreEtudesParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $etudeManager->mandatToString($idMandat);
                $data[] = ['y' => $datas];
            }
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => "Nombre d'études du mandat", 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre d\'études par mandat');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} études');
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->yAxis->allowDecimals(false);
        $ob->yAxis->max(null);
        $ob->yAxis->title(['text' => 'Nombre d\'études', 'style' => self::DEFAULT_STYLE]);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Répartition des dépenses HT selon les comptes comptables pour le mandat en cours (trésorerie)
     */
    private function getRepartitionSorties()
    {
        $em = $this->getDoctrine()->getManager();
        $mandat = $this->get('Mgate.etude_manager')->getMaxMandatCc();

        $nfs = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findBy(['mandat' => $mandat]);
        $bvs = $em->getRepository('MgateTresoBundle:BV')->findBy(['mandat' => $mandat]);

        /* Initialisation */
        $comptes = [];
        $comptes['Honoraires BV'] = 0;
        $comptes['URSSAF'] = 0;
        $montantTotal = 0;

        foreach ($nfs as $nf) {
            foreach ($nf->getDetails() as $detail) {
                $compte = $detail->getCompte();
                if (null !== $compte) {
                    $compte = $detail->getCompte()->getLibelle();
                    $montantTotal += $detail->getMontantHT();
                    if (array_key_exists($compte, $comptes)) {
                        $comptes[$compte] += $detail->getMontantHT();
                    } else {
                        $comptes[$compte] = $detail->getMontantHT();
                    }
                }
            }
        }

        foreach ($bvs as $bv) {
            $comptes['Honoraires BV'] += $bv->getRemunerationBrute();
            $comptes['URSSAF'] += $bv->getPartJunior();
            $montantTotal += $bv->getRemunerationBrute() + $bv->getPartJunior();
        }

        if (0 == $montantTotal) {
            $montantTotal = 1; //set to 1 to avoid division by 0
        }

        ksort($comptes);
        $data = [];
        foreach ($comptes as $compte => $montantHT) {
            $data[] = ['name' => $compte, 'y' => 100 * $montantHT / $montantTotal, 'montantHT' => $montantHT, 'montantTotal' => $montantTotal];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Répartition des dépenses HT', 'data' => $data, 'Dépenses totale' => $montantTotal]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Répartition des dépenses HT selon les comptes comptables<br/>(Mandat en cours)');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.name} : {point.montantHT:,.2f} € / {point.montantTotal:,.2f} € ({point.percentage:.1f}%)');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Montant HT des dépenses (trésorerie)
     */
    private function getSortie()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $sortiesParMandat = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findAllByMandat();
        $bvsParMandat = $em->getRepository('MgateTresoBundle:BV')->findAllByMandat();

        $categories = [];

        $comptes = [];
        $comptes['Honoraires BV'] = [];
        $comptes['URSSAF'] = [];
        $mandats = [];
        ksort($sortiesParMandat); // Tri selon les mandats
        foreach ($sortiesParMandat as $mandat => $nfs) { // Pour chaque Mandat
            $mandats[] = $mandat;
            foreach ($nfs as $nf) { // Pour chaque NF d'un mandat
                foreach ($nf->getDetails() as $detail) { // Pour chaque détail d'une NF
                    $compte = $detail->getCompte();
                    if (null !== $compte) {
                        $compte = $detail->getCompte()->getLibelle();
                        if (array_key_exists($compte, $comptes)) {
                            if (array_key_exists($mandat, $comptes[$compte])) {
                                $comptes[$compte][$mandat] += $detail->getMontantHT();
                            } else {
                                $comptes[$compte][$mandat] = $detail->getMontantHT();
                            }
                        } else {
                            $comptes[$compte] = [$mandat => $detail->getMontantHT()];
                        }
                    }
                }
            }
        }
        foreach ($bvsParMandat as $mandat => $bvs) { // Pour chaque Mandat
            if (!in_array($mandat, $mandats)) {
                $mandats[] = $mandat;
            }
            $comptes['Honoraires BV'][$mandat] = 0;
            $comptes['URSSAF'][$mandat] = 0;
            foreach ($bvs as $bv) {
                // Pour chaque BV d'un mandat
                $comptes['Honoraires BV'][$mandat] += $bv->getRemunerationBrute();
                $comptes['URSSAF'][$mandat] += $bv->getPartJunior();
            }
        }

        $series = [];
        ksort($mandats);
        ksort($comptes);
        foreach ($comptes as $libelle => $compte) {
            $data = [];
            foreach ($mandats as $mandat) {
                if (array_key_exists($mandat, $compte)) {
                    $data[] = round((float) $compte[$mandat], 2);
                } else {
                    $data[] = 0;
                }
            }
            $series[] = ['name' => $libelle, 'data' => $data];
        }

        foreach ($mandats as $mandat) {
            $categories[] = $etudeManager->mandatToString($mandat);
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);
        $ob->plotOptions->column(['stacking' => 'normal']);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Montant HT des dépenses');
        $ob->yAxis->title(['text' => 'Montant (€)', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} € HT');

        $ob->legend->enabled(true);
        $ob->legend->floatinf(false);
        $ob->legend->layout('vertical');
        $ob->legend->y(20);
        $ob->legend->verticalAlign('bottom');
        $ob->legend->align('right');
        $ob->legend->backgroundColor('#F6F6F6');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Taux de fidélisation (développement commercial)
     */
    private function getPartClientFidel()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $clients = [];
        foreach ($etudes as $etude) {
            if (self::STATE_ID_EN_COURS_X == $etude->getStateID() || self::STATE_ID_TERMINEE_X == $etude->getStateID()) {
                $clientID = $etude->getProspect()->getId();
                if (array_key_exists($clientID, $clients)) {
                    ++$clients[$clientID];
                } else {
                    $clients[$clientID] = 1;
                }
            }
        }

        $repartitions = [];
        $nombreClient = count($clients);
        foreach ($clients as $clientID => $nombreEtude) {
            if (array_key_exists($nombreEtude, $repartitions)) {
                ++$repartitions[$nombreEtude];
            } else {
                $repartitions[$nombreEtude] = 1;
            }
        }

        /* Initialisation */
        $data = [];
        ksort($repartitions);
        foreach ($repartitions as $occ => $nbr) {
            $clientType = 1 == $occ ? "$nbr Nouveaux clients" : "$nbr Anciens clients avec $occ études";
            $data[] = ['name' => $clientType, 'y' => 100 * $nbr / $nombreClient];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Taux de fidélisation', 'data' => $data, 'Nombre de clients' => $nombreClient]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Taux de fidélisation (% de clients ayant demandé plusieurs études)');
        $ob->tooltip->headerFormat('<b>{point.key}</b><br/>');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombre de présents aux formations (formations)
     */
    private function getNombreDePresentFormationsTimed()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();
        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        $maxMandat = max(array_keys($formationsParMandat));
        $mandats = [];

        foreach ($formationsParMandat as $mandat => $formations) {
            foreach ($formations as $formation) {
                if ($formation->getDateDebut()) {
                    $interval = new \DateInterval('P' . ($maxMandat - $mandat) . 'Y');
                    $dateDecale = clone $formation->getDateDebut();
                    $dateDecale->add($interval);
                    $mandats[$mandat][] = [
                        'x' => $dateDecale->getTimestamp() * 1000,
                        'y' => count($formation->getMembresPresents()), 'name' => $formation->getTitre(),
                        'date' => $dateDecale->format('d/m/Y'),
                    ];
                }
            }
        }

        $series = [];
        foreach ($mandats as $mandat => $data) {
            $series[] = ['name' => 'Mandat ' . $etudeManager->mandatToString($mandat), 'data' => $data];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newLineChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->global->useUTC(false);

        $ob->title->text('Nombre de présents aux formations');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br/>');
        $ob->tooltip->pointFormat('{point.y} présents le {point.date}<br/>{point.name}');
        $ob->xAxis->dateTimeLabelFormats(['month' => '%b']);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->xAxis->title(['text' => 'Date', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->type('datetime');
        $ob->yAxis->allowDecimals(false);
        $ob->yAxis->title(['text' => 'Nombre de présents', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->yAxis->min(0);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombre de formations par mandat (formations)
     */
    private function getNombreFormationsParMandat()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        $data = [];
        $categories = [];

        ksort($formationsParMandat); // Tri selon les promos
        foreach ($formationsParMandat as $mandat => $formations) {
            $data[] = count($formations);
            $categories[] = $etudeManager->mandatToString($mandat);
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => 'Nombre de formations', 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre de formations théorique par mandat');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br/>');
        $ob->tooltip->pointFormat('{point.y} formations');
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->yAxis->title(['text' => 'Nombre de formations', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Taux d'avenants par mandat (gestion d'études)
     * Basé sur les dates de signature CC et non pas les numéros
     */
    private function getTauxDAvenantsParMandat()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy([], ['dateSignature' => 'asc']);

        $nombreEtudesParMandat = [];
        $nombreEtudesAvecAvenantParMandat = [];

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesParMandat[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesAvecAvenantParMandat[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = self::STATE_ID_EN_COURS_X == $etude->getStateID()
                || self::STATE_ID_TERMINEE_X == $etude->getStateID();

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                ++$nombreEtudesParMandat[$idMandat];
                if (count($etude->getAvs()->toArray())) {
                    ++$nombreEtudesAvecAvenantParMandat[$idMandat];
                }
            }
        }

        $data = [];
        $categories = [];
        foreach ($nombreEtudesParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $etudeManager->mandatToString($idMandat);
                $data[] = ['y' => 100 * $nombreEtudesAvecAvenantParMandat[$idMandat] / $datas, 'nombreEtudes' => $datas, 'nombreEtudesAvecAv' => $nombreEtudesAvecAvenantParMandat[$idMandat]];
            }
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => "Taux d'avenants par mandat", 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Taux d\'avenants du mandat');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y:.2f} %<br/>avec {point.nombreEtudesAvecAv} sur {point.nombreEtudes} études');
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->yAxis->max(100);
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->title(['text' => 'Taux (%)', 'style' => self::DEFAULT_STYLE]);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Répartition du CA selon le type de client (développement commercial)
     */
    private function getRepartitionClientSelonChiffreAffaire()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $chiffreDAffairesTotal = 0;
        $repartitions = [];
        foreach ($etudes as $etude) {
            if (self::STATE_ID_EN_COURS_X == $etude->getStateID() || self::STATE_ID_TERMINEE_X == $etude->getStateID()) {
                $type = $etude->getProspect()->getEntiteToString();
                $CA = $etude->getMontantHT();
                $chiffreDAffairesTotal += $CA;
                array_key_exists($type, $repartitions) ? $repartitions[$type] += $CA : $repartitions[$type] = $CA;
            }
        }

        $data = [];
        foreach ($repartitions as $type => $CA) {
            if (null == $type) {
                $type = 'Autre';
            }
            $data[] = ['name' => $type, 'y' => round($CA / $chiffreDAffairesTotal * 100, 2), 'CA' => $CA];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Provenance du CA selon le type de client (tous mandats)', 'data' => $data, 'CA Total' => $chiffreDAffairesTotal]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text("Répartition du CA selon le type de client ($chiffreDAffairesTotal € CA)");
        $ob->tooltip->headerFormat('<b>{point.key}</b><br />');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %<br/>{point.CA} €');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Provenance des études selon le type de client (développement commercial)
     */
    private function getRepartitionClientParNombreDEtude()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $nombreClient = 0;
        $repartitions = [];

        foreach ($etudes as $etude) {
            if (self::STATE_ID_EN_COURS_X == $etude->getStateID() || self::STATE_ID_TERMINEE_X == $etude->getStateID()) {
                ++$nombreClient;
                $type = $etude->getProspect()->getEntiteToString();
                array_key_exists($type, $repartitions) ? $repartitions[$type]++ : $repartitions[$type] = 1;
            }
        }

        $data = [];
        foreach ($repartitions as $type => $nombre) {
            if (null == $type) {
                $type = 'Autre';
            }
            $data[] = ['name' => $type, 'y' => round($nombre / $nombreClient * 100, 2), 'nombre' => $nombre];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Provenance des études selon le type de client (tous mandats)', 'data' => $data, 'nombreClient' => $nombreClient]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Provenance des études selon le type de client (' . $nombreClient . ' Etudes)');
        $ob->tooltip->headerFormat('<b>{point.key}</b><br />');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %<br/>{point.nombre} études');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    private function cmp($a, $b)
    {
        if ($a['date'] == $b['date']) {
            return 0;
        }

        return ($a['date'] < $b['date']) ? -1 : 1;
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombre de membres (gestion associative)
     */
    private function getNombreMembres()
    {
        $em = $this->getDoctrine()->getManager();
        $mandats = $em->getRepository('MgatePersonneBundle:Mandat')->getCotisantMandats();

        $promos = [];
        $cumuls = [];
        $dates = [];
        foreach ($mandats as $mandat) {
            if ($membre = $mandat->getMembre()) {
                $p = $membre->getPromotion();
                if (!in_array($p, $promos)) {
                    $promos[] = $p;
                }
                $dates[] = ['date' => $mandat->getDebutMandat(), 'type' => '1', 'promo' => $p];
                $dates[] = ['date' => $mandat->getFinMandat(), 'type' => '-1', 'promo' => $p];
            }
        }
        sort($promos);
        usort($dates, [$this, 'cmp']);

        foreach ($dates as $date) {
            $d = $date['date']->format('m/y');
            $p = $date['promo'];
            $t = $date['type'];
            foreach ($promos as $promo) {
                if (!array_key_exists($promo, $cumuls)) {
                    $cumuls[$promo] = [];
                }
                $cumuls[$promo][$d] = (array_key_exists($d, $cumuls[$promo]) ? $cumuls[$promo][$d] : (end($cumuls[$promo]) ? end($cumuls[$promo]) : 0));
            }
            $cumuls[$p][$d] += $t;
        }

        $series = [];
        $categories = array_keys($cumuls[$promos[0]]);
        foreach (array_reverse($promos) as $promo) {
            $series[] = ['name' => 'P' . $promo, 'data' => array_values($cumuls[$promo])];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newLineChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->chart->type('area');
        $ob->chart->zoomType('x');
        $ob->xAxis->categories($categories);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->xAxis->title(['text' => 'Date', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->allowDecimals(false);
        $ob->yAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->yAxis->min(0);
        $ob->yAxis->title(['text' => 'Nombre de membres', 'style' => self::DEFAULT_STYLE]);
        $ob->plotOptions->area(['stacking' => 'normal']);
        $ob->title->text('Nombre de membres (zoomable)');
        $ob->tooltip->shared(true);
        $ob->tooltip->valueSuffix(' cotisants');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombres de membres par promotion (gestion associative)
     */
    private function getMembresParPromo()
    {
        $em = $this->getDoctrine()->getManager();
        $membres = $em->getRepository('MgatePersonneBundle:Membre')->findAll();

        $promos = [];
        foreach ($membres as $membre) {
            $p = $membre->getPromotion();
            if ($p) {
                array_key_exists($p, $promos) ? $promos[$p]++ : $promos[$p] = 1;
            }
        }

        $data = [];
        $categories = [];
        ksort($promos); // Tri selon les promos
        foreach ($promos as $promo => $nombre) {
            $data[] = $nombre;
            $categories[] = 'P' . $promo;
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => 'Membres', 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->xAxis->title(['text' => 'Promotion', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);
        $ob->yAxis->title(['text' => 'Nombre de membres', 'style' => self::DEFAULT_STYLE]);
        $ob->title->text('Nombre de membres par promotion');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br/>');
        $ob->tooltip->pointFormat('{point.y}');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Nombre d'intervenants par promotion (gestion associative)
     */
    private function getIntervenantsParPromo()
    {
        $em = $this->getDoctrine()->getManager();
        $intervenants = $em->getRepository('MgatePersonneBundle:Membre')->getIntervenantsParPromo();

        $promos = [];
        foreach ($intervenants as $intervenant) {
            $p = $intervenant->getPromotion();
            if ($p) {
                array_key_exists($p, $promos) ? $promos[$p]++ : $promos[$p] = 1;
            }
        }

        $data = [];
        $categories = [];
        foreach ($promos as $promo => $nombre) {
            $data[] = $nombre;
            $categories[] = 'P' . $promo;
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['name' => 'Intervenants', 'colorByPoint' => true, 'data' => $data]];
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->xAxis->title(['text' => 'Promotion', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);
        $ob->yAxis->title(['text' => "Nombre d'intervenants", 'style' => self::DEFAULT_STYLE]);
        $ob->title->text('Nombre d\'intervenants par promotion');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y}');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Chiffre d'affaires signé cumulé par mandat (trésorerie)
     */
    private function getCAM()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy([], ['dateSignature' => 'asc']);

        $cumuls = [];
        $cumulsJEH = [];
        $cumulsFrais = [];

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumulsJEH[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumulsFrais[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = self::STATE_ID_EN_COURS_X == $etude->getStateID()
                || self::STATE_ID_TERMINEE_X == $etude->getStateID();

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etude->getMontantHT();
                $cumulsJEH[$idMandat] += $etude->getNbrJEH();
                $cumulsFrais[$idMandat] += $etude->getFraisDossier();
            }
        }

        $data = [];
        $categories = [];
        foreach ($cumuls as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $etudeManager->mandatToString($idMandat);
                $data[] = ['y' => $datas, 'JEH' => $cumulsJEH[$idMandat], 'moyJEH' => ($datas - $cumulsFrais[$idMandat]) / $cumulsJEH[$idMandat]];
            }
        }

        $series = [
            [
                'name' => 'CA signé',
                'colorByPoint' => true,
                'data' => $data,
                'dataLabels' => [
                    'enabled' => true,
                    'rotation' => -90,
                    'align' => 'right',
                    'format' => '{point.y}€',
                    'style' => [
                        'color' => '#FFFFFF',
                        'fontSize' => '18px',
                        'fontFamily' => 'Verdana, sans-serif',
                        'textShadow' => '0 0 2px black', ],
                    'y' => 1,
                ],
            ],
        ];

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE, 'rotation' => -45]);
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);
        $ob->yAxis->title(['text' => 'CA (€)', 'style' => self::DEFAULT_STYLE]);
        $ob->title->text('Chiffre d\'affaires signé cumulé par mandat');
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} €<br/>En {point.JEH} JEH<br/>Soit {point.moyJEH:.2f} €/JEH');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Evolution par mandat du chiffre d'affaires signé cumulé (trésorerie)
     */
    private function getCA()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $Ccs = $this->getDoctrine()->getManager()->getRepository('MgateSuiviBundle:Cc')->findBy([], ['dateSignature' => 'asc']);
        if ($this->get('app.json_key_value_store')->exists('namingConvention')) {
            $namingConvention = $this->get('app.json_key_value_store')->get('namingConvention');
        } else {
            $namingConvention = 'id';
        }
        $mandats = [];
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = [];
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = self::STATE_ID_EN_COURS_X == $etude->getStateID()
                || self::STATE_ID_TERMINEE_X == $etude->getStateID();

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etude->getMontantHT();

                $interval = new \DateInterval('P' . ($maxMandat - $idMandat) . 'Y');
                $dateDecale = clone $dateSignature;
                $dateDecale->add($interval);

                $mandats[$idMandat][]
                    = ['x' => $dateDecale->getTimestamp() * 1000,
                    'y' => $cumuls[$idMandat], 'name' => $etude->getReference($namingConvention) . ' - ' . $etude->getNom(),
                    'date' => $dateDecale->format('d/m/Y'),
                    'prix' => $etude->getMontantHT(), ];
            }
        }

        $series = [];
        foreach ($mandats as $idMandat => $data) {
            $series[] = ['name' => 'Mandat ' . $etudeManager->mandatToString($idMandat), 'data' => $data];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newLineChart($series);

        $ob->chart->renderTo(__FUNCTION__);  // The #id of the div where to render the chart
        $ob->global->useUTC(false);
        $ob->title->text('Évolution par mandat du chiffre d\'affaires signé cumulé');
        $ob->title->style(['fontWeight' => 'bold', 'fontSize' => '20px']);
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} le {point.date}<br />{point.name} à {point.prix} €');
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->xAxis->dateTimeLabelFormats(['month' => '%b']);
        $ob->xAxis->title(['text' => 'Date', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->xAxis->type('datetime');
        $ob->yAxis->min(0);
        $ob->yAxis->title(['text' => "Chiffre d'affaire signé cumulé (€)", 'style' => self::DEFAULT_STYLE]);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Not used at the moment
     */
    private function getRh()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $missions = $this->getDoctrine()->getManager()->getRepository('MgateSuiviBundle:Mission')->findBy([], ['debutOm' => 'asc']);
        if ($this->get('app.json_key_value_store')->exists('namingConvention')) {
            $namingConvention = $this->get('app.json_key_value_store')->get('namingConvention');
        } else {
            $namingConvention = 'id';
        }
        $mandats = [];
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = [];
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }

        $mandats[1] = [];

        //Etape 1 remplir toutes les dates
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateDebut = $mission->getdebutOm();
            $dateFin = $mission->getfinOm();

            if ($dateDebut && $dateFin) {
                $idMandat = $etudeManager->dateToMandat($dateDebut);

                ++$cumuls[0];

                $dateDebutDecale = clone $dateDebut;
                $dateFinDecale = clone $dateFin;

                $addDebut = true;
                $addFin = true;
                foreach ($mandats[1] as $datePoint) {
                    if (($dateDebutDecale->getTimestamp() * 1000) == $datePoint['x']) {
                        $addDebut = false;
                    }
                    if (($dateFinDecale->getTimestamp() * 1000) == $datePoint['x']) {
                        $addFin = false;
                    }
                }

                if ($addDebut) {
                    $mandats[1][]
                        = ['x' => $dateDebutDecale->getTimestamp() * 1000,
                        'y' => 0/* $cumuls[0] */, 'name' => $etude->getReference($namingConvention) . ' + ' . $etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etude->getMontantHT(), ];
                }
                if ($addFin) {
                    $mandats[1][]
                        = ['x' => $dateFinDecale->getTimestamp() * 1000,
                        'y' => 0/* $cumuls[0] */, 'name' => $etude->getReference($namingConvention) . ' - ' . $etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etude->getMontantHT(), ];
                }
            }
        }

        //Etapes 2 trie dans l'ordre
        $callback = function ($a, $b) use ($mandats) {
            return $mandats[1][$a]['x'] > $mandats[1][$b]['x'];
        };
        uksort($mandats[1], $callback);
        foreach ($mandats[1] as $entree) {
            $mandats[2][] = $entree;
        }
        $mandats[1] = [];

        //Etapes 3 ++ --
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateFin = $mission->getfinOm();
            $dateDebut = $mission->getdebutOm();

            if ($dateDebut && $dateFin) {
                $dateDebutDecale = clone $dateDebut;
                $dateFinDecale = clone $dateFin;

                foreach ($mandats[2] as &$entree) {
                    if ($entree['x'] >= $dateDebutDecale->getTimestamp() * 1000 && $entree['x'] < $dateFinDecale->getTimestamp() * 1000) {
                        ++$entree['y'];
                    }
                }
            }
        }

        // Chart
        $series = [];
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = ['name' => 'Mandat ' . $idMandat . ' - ' . $etudeManager->mandatToString($idMandat), 'data' => $data];
        }

        $style = ['color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px'];

        $ob = new Highchart();
        $ob->global->useUTC(false);

        //WARN :::

        $ob->chart->renderTo('getRh');  // The #id of the div where to render the chart
        ///
        $ob->chart->type('spline');
        $ob->xAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->yAxis->labels(['style' => self::DEFAULT_STYLE]);
        $ob->title->text("Évolution par mandat du nombre d'intervenant");
        $ob->title->style(['fontWeight' => 'bold', 'fontSize' => '20px']);
        $ob->xAxis->title(['text' => 'Date', 'style' => self::DEFAULT_STYLE]);
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(['month' => '%b']);
        $ob->yAxis->min(0);
        $ob->yAxis->title(['text' => "Nombre d'intervenant", 'style' => self::DEFAULT_STYLE]);
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(90);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(['lineWidth' => 5, 'marker' => ['radius' => 8]]);
        $ob->series($series);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Provenance des études selon la source de prospection (développement commercial)
     */
    private function getSourceProspectionParNombreDEtude()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $nombreClient = 0;
        $repartitions = [];
        foreach ($etudes as $etude) {
            if (self::STATE_ID_EN_COURS_X == $etude->getStateID() || self::STATE_ID_TERMINEE_X == $etude->getStateID()) {
                ++$nombreClient;
                $type = $etude->getSourceDeProspectionToString();
                array_key_exists($type, $repartitions) ? $repartitions[$type]++ : $repartitions[$type] = 1;
            }
        }

        $data = [];
        foreach ($repartitions as $type => $nombre) {
            if (null == $type) {
                $type = 'Autre';
            }
            $data[] = ['name' => $type, 'y' => round($nombre / $nombreClient * 100, 2), 'nombre' => $nombre];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Provenance des études selon la source de prospection (tous mandats)', 'data' => $data, 'nombreClient' => $nombreClient]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Provenance des études selon la source de prospection (' . $nombreClient . ' Etudes)');
        $ob->tooltip->headerFormat('<b>{point.key}</b><br />');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %<br/>{point.nombre} études');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * Répartition du CA selon la source de prospection (développement commercial)
     */
    private function getSourceProspectionSelonChiffreAffaire()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $chiffreDAffairesTotal = 0;
        $repartitions = [];
        foreach ($etudes as $etude) {
            if (self::STATE_ID_EN_COURS_X == $etude->getStateID() || self::STATE_ID_TERMINEE_X == $etude->getStateID()) {
                $type = $etude->getSourceDeProspectionToString();
                $CA = $etude->getMontantHT();
                $chiffreDAffairesTotal += $CA;
                array_key_exists($type, $repartitions) ? $repartitions[$type] += $CA : $repartitions[$type] = $CA;
            }
        }

        $data = [];
        foreach ($repartitions as $type => $CA) {
            if (null == $type) {
                $type = 'Autre';
            }
            $data[] = ['name' => $type, 'y' => round($CA / $chiffreDAffairesTotal * 100, 2), 'CA' => $CA];
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = [['type' => 'pie', 'name' => 'Répartition du CA selon la source de prospection (tous mandats)', 'data' => $data, 'CA Total' => $chiffreDAffairesTotal]];
        $ob = $chartFactory->newPieChart($series);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text("Répartition du CA selon la source de prospection ($chiffreDAffairesTotal € CA)");
        $ob->tooltip->headerFormat('<b>{point.key}</b><br />');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %<br/>{point.CA} €');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     *
     * A chart displaying how much a skill has brought in turnover
     */
    private function getCACompetences()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $MANDAT_MAX = $etudeManager->getMaxMandat();
        $MANDAT_MIN = $etudeManager->getMinMandat();

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('N7consultingRhBundle:Competence')->getAllEtudesByCompetences();

        //how much each skill has make us earn.
        $series = [];
        $categories = [];
        $used_mandats = array_fill(0, $MANDAT_MAX - $MANDAT_MIN + 1, 0); // an array to post-process results and remove mandats without data.
        //create array structure
        foreach ($res as $c) {
            $temp = [
                'name' => $c->getNom(),
                'data' => array_fill(0, $MANDAT_MAX - $MANDAT_MIN + 1, 0),
            ];

            $sumSkill = 0;
            foreach ($c->getEtudes() as $e) {
                $temp['data'][$e->getMandat() - $MANDAT_MIN] += $e->getMontantHT();
                $used_mandats[$e->getMandat() - $MANDAT_MIN] += 1;
                $sumSkill += $e->getMontantHT();
            }
            if ($sumSkill > 0) {
                $series[] = $temp;
            }
        }

        for ($i = $MANDAT_MIN; $i <= $MANDAT_MAX; ++$i) {
            $categories[] = $etudeManager->mandatToString($i);
        }

        //remove mandats with no skills used
        //once array has been spliced, index will be changed. Therefore, we uses $k has read index
        $k = 0;
        for ($i = 0; $i <= $MANDAT_MAX - $MANDAT_MIN; ++$i) {
            if (0 == $used_mandats[$i] && isset($categories[$k])) {
                array_splice($categories, $k, 1);
                $count_series = count($series);
                for ($j = 0; $j < $count_series; ++$j) {
                    array_splice($series[$j]['data'], $k, 1);
                }
            } else {
                ++$k;
            }
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->xAxis->title(['text' => 'Mandat', 'style' => self::DEFAULT_STYLE]);
        $ob->yAxis->max(null);
        $ob->yAxis->title(['text' => 'Revenus (€)', 'style' => self::DEFAULT_STYLE]);
        $ob->title->text('Revenus par compétences');
        $ob->tooltip->headerFormat('<b>Mandat {point.x}</b><br/>');

        $ob->legend->enabled(true);
        $ob->legend->backgroundColor('#F6F6F6');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', [
            'chart' => $ob,
        ]);
    }
}

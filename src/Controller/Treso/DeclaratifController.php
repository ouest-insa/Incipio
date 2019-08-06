<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Treso;

use App\Entity\Treso\BV;
use App\Entity\Treso\Facture;
use App\Entity\Treso\NoteDeFrais;
use App\Entity\Treso\TresoDetailableInterface;
use App\Entity\Treso\TresoDetailInterface;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\DateType as GenemuDateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratifController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Declaratif_index", path="/Tresorerie/Declaratifs", methods={"GET","HEAD"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $bvs = $em->getRepository(BV::class)->findAll();

        return $this->render('Treso/BV/index.html.twig', ['bvs' => $bvs]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Declaratif_TVA", path="/Tresorerie/Declaratifs/TVA/{year}/{month}/{trimestriel}", methods={"GET","HEAD","POST"}, defaults={"year": "", "month": "", "trimestriel": ""})
     *
     * @param Request $request
     * @param int     $year
     * @param int     $month
     * @param bool    $trimestriel
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function tva(Request $request, $year, $month, bool $trimestriel)
    {
        // In case no date is specified, take current month
        if ('' === $year || '' === $month) {
            $date = new \DateTime('now');
            $month = $date->format('m');
            $year = $date->format('Y');
        } else { // rebuil a valid \Datetime
            $date = new \DateTime($year . '-' . $month . '-01');
        }
        setlocale(LC_TIME, 'fra_fra');
        /** Date Management form */
        $form = $this->createFormBuilder(['message' => 'Date'])
            ->add(
                'date', GenemuDateType::class,
                [
                    'label' => 'Mois considéré',
                    'required' => true,
                    'widget' => 'single_text',
                    'data' => null === $year || null === $month ? date_create() : new \DateTime($year . '-' . $month . '-01'),
                    'format' => 'dd/MM/yyyy', ])
            ->add('trimestriel', CheckboxType::class, ['label' => 'Trimestriel ?', 'required' => false])
            ->getForm();

        if ($request->isMethod('POST')) {
            //small hack to keep api working
            $form->handleRequest($request);
            $data = $form->getData();
            /** @var \DateTime $date */
            $date = $data['date'];

            return $this->redirectToRoute('treso_Declaratif_TVA', ['year' => $date->format('Y'),
                'month' => $date->format('m'),
                'trimestriel' => $data['trimestriel'],
            ]);
        }

        $tvaCollectee = [];
        $tvaDeductible = [];
        $totalTvaCollectee = ['HT' => 0, 'TTC' => 0, 'TVA' => 0];
        $totalTvaDeductible = ['HT' => 0, 'TTC' => 0, 'TVA' => 0];
        $tvas = [];
        $nfs = [];
        $fas = [];
        $fvs = [];
        $em = $this->getDoctrine()->getManager();

        if ($trimestriel) {
            $periode = 'Déclaratif pour la période : ' . date('F Y', $date->getTimestamp()) . ' - ' . date('F Y', $date->modify('+2 month')->getTimestamp());
            for ($i = 0; $i < 3; ++$i) {
                $nfs = $em->getRepository(NoteDeFrais::class)->findAllByMonth($month, $year, true);
                $fas = $em->getRepository(Facture::class)->findAllTVAByMonth(Facture::TYPE_ACHAT, $month, $year, true);
                $fvs = $em->getRepository(Facture::class)->findAllTVAByMonth(Facture::TYPE_VENTE, $month, $year, true);
            }
        } else {
            $periode = 'Déclaratif pour la période : ' . date('F Y', $date->getTimestamp());
            $nfs = $em->getRepository(NoteDeFrais::class)->findAllByMonth($month, $year);
            $fas = $em->getRepository(Facture::class)->findAllTVAByMonth(Facture::TYPE_ACHAT, $month, $year);
            $fvs = $em->getRepository(Facture::class)->findAllTVAByMonth(Facture::TYPE_VENTE, $month, $year);
        }

        /*
         * TVA DEDUCTIBLE
         */
        foreach ([$fas, $nfs] as $entityDeductibles) {
            /** @var TresoDetailableInterface $entityDeductible */
            foreach ($entityDeductibles as $entityDeductible) {
                $montantTvaParType = [];
                $montantHT = 0;
                $montantTTC = 0;
                /** @var TresoDetailInterface $entityDeductibled */
                foreach ($entityDeductible->getDetails() as $entityDeductibled) {
                    $tauxTVA = $entityDeductibled->getTauxTVA();
                    if (array_key_exists($tauxTVA, $montantTvaParType)) {
                        $montantTvaParType[$tauxTVA] += $entityDeductibled->getMontantTVA();
                    } else {
                        $montantTvaParType[$tauxTVA] = $entityDeductibled->getMontantTVA();
                    }
                    $montantHT += $entityDeductibled->getMontantHT();
                    $montantTTC += $entityDeductibled->getMontantTTC();

                    // mise à jour des montant Globaux
                    $totalTvaDeductible['HT'] += $entityDeductibled->getMontantHT();
                    $totalTvaDeductible['TTC'] += $entityDeductibled->getMontantTTC();
                    $totalTvaDeductible['TVA'] += $entityDeductibled->getMontantTVA();

                    // Mise à jour du montant global pour le taux de TVA ciblé
                    if (!in_array($tauxTVA, $tvas) && null !== $tauxTVA) {
                        $tvas[] = $tauxTVA;
                    }
                    if (!array_key_exists($tauxTVA, $totalTvaDeductible)) {
                        $totalTvaDeductible[$tauxTVA] = $entityDeductibled->getMontantTVA();
                    } else {
                        $totalTvaDeductible[$tauxTVA] += $entityDeductibled->getMontantTVA();
                    }
                }
                $tvaDeductible[] = ['DATE' => $entityDeductible->getDate(), 'LI' => $entityDeductible->getReference(), 'HT' => $montantHT, 'TTC' => $montantTTC, 'TVA' => $entityDeductible->getMontantTVA(), 'TVAT' => $montantTvaParType];
            }
        }

        /*
         * TVA COLLECTE
         */
        /** @var Facture $fv */
        foreach ($fvs as $fv) {
            $montantTvaParType = [];

            $montantHT = $fv->getMontantHT();
            $montantTTC = $fv->getMontantTVA();

            // Mise à jour du montant global pour le taux de TVA ciblé
            $totalTvaCollectee['HT'] += $fv->getMontantHT();
            $totalTvaCollectee['TTC'] += $fv->getMontantTTC();
            $totalTvaCollectee['TVA'] += $fv->getMontantTVA();

            foreach ($fv->getDetails() as $fvd) {
                $tauxTVA = $fvd->getTauxTVA();
                if (array_key_exists($tauxTVA, $montantTvaParType)) {
                    $montantTvaParType[$tauxTVA] += $fvd->getMontantTVA();
                } else {
                    $montantTvaParType[$tauxTVA] = $fvd->getMontantTVA();
                }

                if (!array_key_exists($tauxTVA, $totalTvaCollectee)) {
                    $totalTvaCollectee[$tauxTVA] = $fvd->getMontantTVA();
                } else {
                    $totalTvaCollectee[$tauxTVA] += $fvd->getMontantTVA();
                }

                // Ajout de l'éventuel nouveau taux de TVA à la liste des taux
                if (!in_array($tauxTVA, $tvas) && null !== $tauxTVA) {
                    $tvas[] = $tauxTVA;
                }
            }
            if ($md = $fv->getMontantADeduire()) {
                $tauxTVA = $md->getTauxTVA();
                if (array_key_exists($tauxTVA, $montantTvaParType)) {
                    $montantTvaParType[$tauxTVA] -= $md->getMontantTVA();
                } else {
                    $montantTvaParType[$tauxTVA] = -$md->getMontantTVA();
                }

                if (!array_key_exists($tauxTVA, $totalTvaCollectee)) {
                    $totalTvaCollectee[$tauxTVA] = -$md->getMontantTVA();
                } else {
                    $totalTvaCollectee[$tauxTVA] -= $md->getMontantTVA();
                }

                // Ajout de l'éventuel nouveau taux de TVA à la liste des taux
                if (!in_array($tauxTVA, $tvas) && null !== $tauxTVA) {
                    $tvas[] = $tauxTVA;
                }
            }

            $tvaCollectee[] = ['DATE' => $fv->getDate(), 'LI' => $fv->getReference(), 'HT' => $montantHT, 'TTC' => $montantTTC, 'TVA' => $fv->getMontantTVA(), 'TVAT' => $montantTvaParType];
        }
        sort($tvas);

        return $this->render('Treso/Declaratif/TVA.html.twig',
            ['form' => $form->createView(),
                'tvas' => $tvas,
                'tvaDeductible' => $tvaDeductible,
                'tvaCollectee' => $tvaCollectee,
                'totalTvaDeductible' => $totalTvaDeductible,
                'totalTvaCollectee' => $totalTvaCollectee,
                'periode' => $periode,
            ]
        );
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Declaratif_BRC", path="/Tresorerie/Declaratifs/BRC/{year}/{month}", methods={"GET","HEAD","POST"}, defaults={"year": "", "month": ""})
     *
     * @param Request $request
     * @param null    $year
     * @param null    $month
     *
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function brc(Request $request, $year, $month)
    {
        if ('' === $year || '' === $month) {
            $date = new \DateTime('now');
            $month = $date->format('m');
            $year = $date->format('Y');
        }

        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder(['message' => 'Date'])
            ->add(
                'date', GenemuDateType::class,
                [
                    'label' => 'Mois du déclaratif',
                    'required' => true, 'widget' => 'single_text',
                    'data' => date_create(), 'format' => 'dd/MM/yyyy', ]
            )->getForm();

        if ($request->isMethod('POST')) {
            //small hack to keep api working
            $form->handleRequest($request);
            $data = $form->getData();
            /** @var \DateTime $date */
            $date = $data['date'];

            return $this->redirectToRoute('treso_Declaratif_BRC', ['year' => $date->format('Y'),
                'month' => $date->format('m'),
            ]);
        }

        $bvs = $em->getRepository(BV::class)->findAllByMonth($month, $year);

        $salarieRemunere = [];
        /** @var BV $bv */
        foreach ($bvs as $bv) {
            $id = $bv->getMission()->getIntervenant()->getIdentifiant();
            $salarieRemunere[$id] = 1;
        }

        return $this->render('Treso/Declaratif/BRC.html.twig',
            ['form' => $form->createView(), 'bvs' => $bvs, 'nbSalarieRemunere' => count($salarieRemunere)]
        );
    }
}

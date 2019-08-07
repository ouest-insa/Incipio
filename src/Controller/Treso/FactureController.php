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

use App\Entity\Project\Etude;
use App\Entity\Project\Phase;
use App\Entity\Treso\Compte;
use App\Entity\Treso\Facture;
use App\Entity\Treso\FactureDetail;
use App\Form\Treso\FactureType;
use App\Service\Publish\ConversionLettreFormatter;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class FactureController extends AbstractController
{
    public $keyValueStore;

    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Facture_index", path="/Tresorerie/Factures", methods={"GET","HEAD"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $factures = $em->getRepository(Facture::class)->getFactures();

        return $this->render('Treso/Facture/index.html.twig', ['factures' => $factures]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Facture_ajouter", path="/Tresorerie/Facture/Ajouter", methods={"GET","HEAD","POST"})
     *
     * @param Request                   $request   Http request
     * @param Etude                     $etude     Etude to which the Facture will be added
     * @param ConversionLettreFormatter $formatter
     *
     * @return RedirectResponse|Response
     */
    public function ajouter(Request $request, ?Etude $etude, ConversionLettreFormatter $formatter)
    {
        $em = $this->getDoctrine()->getManager();

        $facture = $this->createFacture($em, $etude, $formatter);
        $form = $this->createForm(FactureType::class, $facture);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($facture->getDetails() as $factured) {
                    $factured->setFacture($facture);
                }

                if ($facture->getType() <= Facture::TYPE_VENTE_ACCOMPTE || null === $facture->getMontantADeduire() || 0 == $facture->getMontantADeduire()
                        ->getMontantHT()
                ) {
                    $facture->setMontantADeduire(null);
                } else {
                    $facture->getMontantADeduire()->setFactureADeduire($facture);
                }

                $em->persist($facture);
                $em->flush();
                $this->addFlash('success', 'Facture ajoutée');

                return $this->redirectToRoute('treso_Facture_voir', ['id' => $facture->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Treso/Facture/modifier.html.twig', [
            'facture' => $facture,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Facture_voir", path="/Tresorerie/Facture/{id}", methods={"GET","HEAD"})
     *
     * @param Facture $facture
     *
     * @return Response
     */
    public function voir(Facture $facture)
    {
        $deleteForm = $this->createDeleteForm($facture);

        return $this->render('Treso/Facture/voir.html.twig', ['facture' => $facture,
                                                              'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_Facture_modifier", path="/Tresorerie/Facture/Modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     * @param Facture $facture
     *
     * @return RedirectResponse|Response
     */
    public function modifier(Request $request, Facture $facture)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(FactureType::class, $facture);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($facture->getDetails() as $factured) {
                    $factured->setFacture($facture);
                }

                if ($facture->getType() <= Facture::TYPE_VENTE_ACCOMPTE || null === $facture->getMontantADeduire() ||
                    0 == $facture->getMontantADeduire()->getMontantHT()
                ) {
                    $facture->setMontantADeduire(null);
                }

                $em->persist($facture);
                $em->flush();
                $this->addFlash('success', 'Facture modifiée');

                return $this->redirectToRoute('treso_Facture_voir', ['id' => $facture->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }
        $deleteForm = $this->createDeleteForm($facture);

        return $this->render('Treso/Facture/modifier.html.twig', [
            'facture' => $facture,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="treso_Facture_supprimer", path="/Tresorerie/Facture/Supprimer/{id}", methods={"DELETE"})
     *
     * @param Facture $facture
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function supprimer(Facture $facture, Request $request)
    {
        $form = $this->createDeleteForm($facture);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($facture);
            $em->flush();

            $this->addFlash('success', 'Facture supprimée');
        } else {
            $this->addFlash('danger', 'Erreur dans le formulaire');
        }

        return $this->redirectToRoute('treso_Facture_index');
    }

    /**
     * @param Facture $facture
     *
     * @return FormInterface
     */
    private function createDeleteForm(Facture $facture)
    {
        return $this->createFormBuilder(['id' => $facture->getId()])
            ->add('id', HiddenType::class)
            ->setmethod('DELETE')
            ->setAction($this->generateUrl('treso_Facture_supprimer', ['id' => $facture->getId()]))
            ->getForm();
    }

    /**
     * Returns a well formatted facture, according to current Etude state.
     *
     * @param ObjectManager             $em
     * @param Etude                     $etude
     * @param ConversionLettreFormatter $formatter
     *
     * @return Facture
     */
    private function createFacture(ObjectManager $em, ?Etude $etude, ConversionLettreFormatter $formatter)
    {
        if (!$this->keyValueStore->exists('tva')) {
            throw new \RuntimeException('Le paramètres tva n\'est pas disponible.');
        }
        $tauxTVA = 100 * $this->keyValueStore->get('tva'); // former value: 20, tva is stored as 0.2 in key-value store
        $compteEtude = 705000;
        $compteFrais = 708500;
        $compteAcompte = 419100;
        if ($this->keyValueStore->exists('namingConvention')) {
            $namingConvention = $this->keyValueStore->get('namingConvention');
        } else {
            $namingConvention = 'id';
        }
        $facture = new Facture();
        $now = new \DateTime('now');
        $facture->setDateEmission($now);

        if ($etude) {
            $facture->setEtude($etude);
            $facture->setBeneficiaire($etude->getProspect());
        }

        if ($etude && !count($etude->getFactures()) && $etude->getAcompte()) {
            $facture->setType(Facture::TYPE_VENTE_ACCOMPTE);
            $facture->setObjet('Facture d\'acompte sur l\'étude ' . $etude->getReference($namingConvention) . ', correspondant au règlement de ' .
                $formatter->moneyFormat(($etude->getPourcentageAcompte() * 100)) . ' % de l’étude.');
            $detail = new FactureDetail();
            $detail->setCompte($em->getRepository(Compte::class)->findOneBy(['numero' => $compteAcompte]));
            $detail->setFacture($facture);
            $facture->addDetail($detail);
            $detail->setDescription('Acompte de ' . $formatter->moneyFormat(($etude->getPourcentageAcompte() * 100)) . ' % sur l\'étude ' .
                $etude->getReference());
            $detail->setMontantHT($etude->getPourcentageAcompte() * $etude->getMontantHT());
            $detail->setTauxTVA($tauxTVA);
        } else {
            $facture->setType(Facture::TYPE_VENTE_SOLDE);
            if ($etude && $etude->getAcompte() && $etude->getFa()) {
                $montantADeduire = new FactureDetail();
                $montantADeduire->setDescription('Facture d\'acompte sur l\'étude ' . $etude->getReference($namingConvention) .
                    ', correspondant au règlement de ' . $formatter->moneyFormat(($etude->getPourcentageAcompte() * 100)) .
                    ' % de l’étude.')
                    ->setFactureADeduire($facture);
                $facture->setMontantADeduire($montantADeduire);
            }

            if ($etude) {
                /** @var Phase $phase */
                foreach ($etude->getPhases() as $phase) {
                    $detail = new FactureDetail();
                    $detail->setCompte($em->getRepository(Compte::class)
                        ->findOneBy(['numero' => $compteEtude]));
                    $detail->setFacture($facture);
                    $facture->addDetail($detail);
                    $detail->setDescription('Phase ' . ($phase->getPosition() + 1) . ' : ' . $phase->getTitre() . ' : ' .
                        $phase->getNbrJEH() . ' JEH * ' . $formatter->moneyFormat($phase->getPrixJEH()) . ' €');
                    $detail->setMontantHT($phase->getPrixJEH() * $phase->getNbrJEH());
                    $detail->setTauxTVA($tauxTVA);
                }

                $detail = new FactureDetail();
                $detail->setCompte($em->getRepository(Compte::class)->findOneBy(['numero' => $compteFrais]))
                    ->setFacture($facture)
                    ->setDescription('Frais de dossier')
                    ->setMontantHT($etude->getFraisDossier());
                $facture->addDetail($detail);
                $detail->setTauxTVA($tauxTVA);
                $facture->setObjet('Facture de Solde sur l\'étude ' . $etude->getReference($namingConvention) . '.');
            }
        }

        return $facture;
    }
}

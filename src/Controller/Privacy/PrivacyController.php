<?php

namespace App\Controller\Privacy;

use App\Entity\Personne\Personne;
use App\Entity\Personne\Prospect;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/privacy")
 */
class PrivacyController extends AbstractController
{
    /** GDPR actions */
    public const GDPR_ACCESS_ACTION = 'access';

    public const GDPR_DELETE_ACTION = 'delete';

    public const GDPR_MODIFY_ACTION = 'modify';

    public const GDPR_EXPORT_ACTION = 'export';

    /**
     * @Security("has_role('ROLE_RGPD')")
     * @Route("/", name="privacy_homepage", methods={"GET"})
     */
    public function index()
    {
        $personnes = $this->getDoctrine()->getManager()
            ->getRepository(Personne::class)
            ->getAllPersonne(true);
        $firms = $this->getDoctrine()->getManager()->getRepository(Prospect::class)
            ->findAll();

        return $this->render('Privacy/Privacy/index.html.twig', [
            'firms' => $firms,
            'personnes' => $personnes,
        ]);
    }

    /**
     * Entrypoint for the four actions of the GDPR.
     *
     * @Security("has_role('ROLE_RGPD')")
     * @Route("/action/{id}", name="privacy_action", methods={"POST"})
     *
     * @param Request             $request
     * @param Personne            $personne
     * @param SerializerInterface $serializer
     *
     * @return RedirectResponse
     */
    public function action(Request $request, Personne $personne, SerializerInterface $serializer)
    {
        if (!$request->request->has('token') ||
            $this->isCsrfTokenValid($request->request->get('token'), 'rgpd')
        ) {
            $this->addFlash('danger', 'Token invalide');

            return $this->redirectToRoute('privacy_homepage');
        }

        if (!$request->request->has('action')) {
            $this->addFlash('danger', 'Formulaire invalide');

            return $this->redirectToRoute('privacy_homepage');
        }

        $action = $request->request->get('action');

        if (self::GDPR_ACCESS_ACTION === $action) {
            return $this->access($personne);
        }

        if (self::GDPR_DELETE_ACTION === $action) {
            return $this->delete($personne);
        }

        if (self::GDPR_MODIFY_ACTION === $action) {
            return $this->modify($personne);
        }

        if (self::GDPR_EXPORT_ACTION === $action) {
            return $this->export($personne, $serializer);
        }

        $this->addFlash('danger', 'Action invalide');

        return $this->redirectToRoute('privacy_homepage');
    }

    private function access(Personne $personne)
    {
        return $this->render('Privacy/Privacy/access.html.twig', ['personne' => $personne]);
    }

    private function delete(Personne $personne)
    {
        $em = $this->getDoctrine()->getManager();
        $personne->anonymize();
        $em->flush();

        try {
            $em->remove($personne);
            $em->flush();
            $this->addFlash('success', 'Personne supprimée');
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('warning', 'La personne a signée des documents et ne
            peux être supprimée sans nuire à l\'intégrité des données réglementaires (historique des missions ...). 
            Le maximum de ses données personnelles ont été supprimées et le reste a été anonymisé');
        }

        return $this->redirectToRoute('privacy_homepage');
    }

    private function modify(Personne $personne)
    {
        if (null !== $personne->getMembre()) {
            return $this->redirectToRoute('personne_membre_modifier', ['id' => $personne->getMembre()->getId()]);
        }
        if (null !== $personne->getEmploye()) {
            return $this->redirectToRoute('personne_employe_modifier', ['id' => $personne->getEmploye()->getId()]);
        }

        $this->addFlash('danger', 'Cette personne n\'est ni un membre ni un ouvrier');

        return $this->redirectToRoute('privacy_homepage');
    }

    private function export(Personne $personne, SerializerInterface $serializer)
    {
        $data = $serializer->serialize($personne, 'json', ['groups' => ['gdpr']]);

        $response = new JsonResponse($data);
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="Export-RGPD-' . date('Y-m-d') . '-' . $personne->getNom() . '.json";');

        return $response;
    }
}

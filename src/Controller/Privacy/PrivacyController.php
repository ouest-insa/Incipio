<?php

namespace N7consulting\PrivacyBundle\Controller;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Mgate\PersonneBundle\Entity\Personne;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PrivacyController extends Controller
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
    public function indexAction()
    {
        $personnes = $this->getDoctrine()->getManager()
            ->getRepository('MgatePersonneBundle:Personne')
            ->getAllPersonne(true);
        $firms = $this->getDoctrine()->getManager()->getRepository('MgatePersonneBundle:Prospect')
            ->findAll();

        return $this->render('N7consultingPrivacyBundle:Privacy:index.html.twig', [
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
     * @param Request  $request
     * @param Personne $personne
     *
     * @return RedirectResponse
     */
    public function actionAction(Request $request, Personne $personne)
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
            return $this->export($personne);
        }

        $this->addFlash('danger', 'Action invalide');

        return $this->redirectToRoute('privacy_homepage');
    }

    private function access(Personne $personne)
    {
        return $this->render('@N7consultingPrivacy/Privacy/access.html.twig', ['personne' => $personne]);
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
            return $this->redirectToRoute('MgatePersonne_membre_modifier', ['id' => $personne->getMembre()->getId()]);
        }
        if (null !== $personne->getEmploye()) {
            return $this->redirectToRoute('MgatePersonne_employe_modifier', ['id' => $personne->getEmploye()->getId()]);
        }

        $this->addFlash('danger', 'Cette personne n\'est ni un membre ni un ouvrier');

        return $this->redirectToRoute('privacy_homepage');
    }

    private function export(Personne $personne)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory)]);

        $data = $serializer->normalize($personne, null, ['groups' => ['gdpr']]);

        $response = new JsonResponse($data);
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="Export-RGPD-' . date('Y-m-d') . '-' . $personne->getNom() . '.json";');

        return $response;
    }
}

<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Publish;


use App\Entity\Formation\Formation;
use App\Entity\Personne\Membre;
use App\Entity\Project\Etude;
use App\Entity\Publish\Document;
use App\Entity\Publish\RelatedDocument;
use App\Form\Publish\DocumentType;
use App\Service\Project\EtudePermissionChecker;
use App\Service\Publish\DocumentManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DocumentController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_publi_documenttype_index", path="/Documents/", methods={"GET","HEAD"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgatePubliBundle:Document')->findAll();

        $totalSize = 0;
        foreach ($entities as $entity) {
            $totalSize += $entity->getSize();
        }

        return $this->render('Publish/Document/index.html.twig', [
            'docs' => $entities,
            'totalSize' => $totalSize,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_publi_document_voir", path="/Documents/show/{id}", methods={"GET","HEAD"})
     *
     * @param Document $documentType (ParamConverter) The document to be downloaded
     *
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function voirAction(Document $documentType)
    {
        $documentStoragePath = $this->get('kernel')->getRootDir() . '' . Document::DOCUMENT_STORAGE_ROOT;
        if (file_exists($documentStoragePath . '/' . $documentType->getPath())) {
            $response = new BinaryFileResponse($documentStoragePath . '/' . $documentType->getPath());
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

            return $response;
        } else {
            throw new \Exception($documentStoragePath . '/' . $documentType->getPath() . ' n\'existe pas');
        }
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="Mgate_publi_document_uploadEtude", path="/Documents/Upload/Etude/{nom}", methods={"GET","HEAD","POST"})
     *
     * @param Request                $request
     * @param Etude                  $etude
     * @param EtudePermissionChecker $permChecker
     *
     * @return Response
     */
    public function uploadEtudeAction(Request $request, Etude $etude, EtudePermissionChecker $permChecker)
    {
        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle !');
        }

        if (!$response = $this->upload($request, false, ['etude' => $etude])) {
            $this->addFlash('success', 'Document mis en ligne');

            return $this->redirectToRoute('MgateSuivi_etude_voir', ['nom' => $etude->getNom()]);
        }

        return $response;
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="Mgate_publi_document_uploadEtudiant", path="/Documents/Upload/Etudiant/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request       $request
     * @param Membre        $membre
     * @param ObjectManager $em
     *
     * @return bool|RedirectResponse|Response
     *
     */
    public function uploadEtudiantAction(Request $request, Membre $membre, ObjectManager $em)
    {
        $options['etudiant'] = $membre;

        if (!$response = $this->upload($request, false, $options)) {
            $this->addFlash('success', 'Document mis en ligne');

            return $this->redirectToRoute('MgatePersonne_membre_voir', ['id' => $membre_id]);
        }

        return $response;
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_publi_document_uploadFormation", path="/Documents/Upload/Formation/{id}", methods={"GET","HEAD"})
     *
     * @param Formation $formation
     *
     * @return JsonResponse
     */
    public function uploadFormationAction(Formation $formation)
    {
        return new JsonResponse([], Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_publi_document_uploadDoctype", path="/Documents/Upload/Doctype", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function uploadDoctypeAction(Request $request)
    {
        if (!$response = $this->upload($request, true)) {
            // Si tout est ok
            return $this->redirectToRoute('Mgate_publi_documenttype_index');
        } else {
            return $response;
        }
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="Mgate_publi_document_delete", path="/Documents/Supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Document $doc
     *
     * @return Response
     */
    public function deleteAction(Document $doc)
    {
        $em = $this->getDoctrine()->getManager();
        $doc->setRootDir($this->get('kernel')->getRootDir());

        if ($doc->getRelation()) { // Cascade sucks
            $relation = $doc->getRelation()->setDocument();
            $doc->setRelation(null);
            $em->remove($relation);
            $em->flush();
        }
        $this->addFlash('success', 'Document supprimé');
        $em->remove($doc);
        $em->flush();

        return $this->redirectToRoute('Mgate_publi_documenttype_index');
    }

    private function upload(Request $request, $deleteIfExist = false, $options = [], DocumentManager $documentManager)
    {
        $document = new Document();
        $document->setRootDir($this->get('kernel')->getRootDir());
        if (count($options)) {
            $relatedDocument = new RelatedDocument();
            $relatedDocument->setDocument($document);
            $document->setRelation($relatedDocument);
            if (array_key_exists('etude', $options)) {
                $relatedDocument->setEtude($options['etude']);
            }
            if (array_key_exists('etudiant', $options)) {
                $relatedDocument->setMembre($options['etudiant']);
            }
        }

        $form = $this->createForm(DocumentType::class, $document, $options);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $documentManager->uploadDocument($document, null, $deleteIfExist);

                return false;
            }
        }

        return $this->render('Publish/Document/upload.html.twig', ['form' => $form->createView()]);
    }
}

<?php

namespace App\Controller\Publish;

use App\Service\Publish\SiajeEtudeImporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    const AVAILABLE_FORMATS = ['Siaje Etudes'];

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_publi_import", path="/Documents/import", methods={"GET","HEAD","POST"})
     *
     * @param Request            $request
     * @param SiajeEtudeImporter $siajeImporter
     *
     * @return Response display an upload form for a csv resources from other crm
     */
    public function indexAction(Request $request, SiajeEtudeImporter $siajeImporter)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder([])->add('import_method', ChoiceType::class, ['label' => 'Type du fichier',
                'required' => true,
                'choices' => $this::AVAILABLE_FORMATS,
                'choice_label' => function ($value) {
                    return $value;
                },
                'expanded' => true,
                'multiple' => false, ]
        )
            ->add('file', FileType::class, ['label' => 'Fichier', 'required' => true, 'attr' => ['cols' => '100%', 'rows' => 5]])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ('Siaje Etudes' == $form->get('import_method')->getData()) {
                $data = $form->getData();

                // Création d'un fichier temporaire
                $file = $data['file'];
                $results = $siajeImporter->run($file);

                $request->getSession()->getFlashBag()->add('success', 'Document importé. ' . $results['inserted_projects'] . ' études créées, ' . $results['inserted_prospects'] . ' prospects créés');

                return $this->redirectToRoute('Mgate_publi_import');
            }
        }

        return $this->render('Publish/Import/index.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_publi_import_format", path="/Documents/import/format/{service_number}", methods={"GET","HEAD"})
     *
     * @param int                $service_number id of service as stated in $this::AVAILABLE_FORMATS
     *                                           Return an html snippet of how csv should be formatted to match import
     * @param SiajeEtudeImporter $siajeImporter
     *
     * @return JsonResponse an array containing expected headers
     */
    public function ajaxExpectedFormatAction($service_number, SiajeEtudeImporter $siajeImporter)
    {
        if ($service_number < count($this::AVAILABLE_FORMATS)) {
            if ('Siaje Etudes' == $this::AVAILABLE_FORMATS[$service_number]) {
                return new JsonResponse($siajeImporter->expectedFormat());
            }
        }

        return new JsonResponse(null);
    }
}

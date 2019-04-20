<?php

namespace Mgate\PubliBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportController extends Controller
{
    const AVAILABLE_FORMATS = ['Siaje Etudes'];

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response display an upload form for a csv resources from other crm
     */
    public function indexAction(Request $request)
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

                $siajeImporter = $this->get('Mgate.import.siaje_etude');

                $results = $siajeImporter->run($file);

                if (!array_key_exists('error', $results) || '' == $results['error']) {
                    $this->addFlash('success', 'Document importé. ' . $results['inserted_projects'] . ' études créées, ' . $results['inserted_prospects'] . ' prospects créés');
                } else {
                    $this->addFlash('danger', $results['error']);
                }

                return $this->redirectToRoute('Mgate_publi_import');
            }
        }

        return $this->render('MgatePubliBundle:Import:index.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param $serviceName integer id of service as stated in $this::AVAILABLE_FORMATS
     * Return an html snippet of how csv should be formatted to match import
     *
     * @return JsonResponse an array containing expected headers
     */
    public function ajaxExpectedFormatAction($serviceName)
    {
        if (in_array($serviceName, self::AVAILABLE_FORMATS)) {
            if ('Siaje Etudes' == $serviceName) {
                $siajeImporter = $this->get('Mgate.import.siaje_etude');

                return new JsonResponse($siajeImporter->expectedFormat());
            }
        }

        return new JsonResponse(null);
    }
}

<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Dashboard;

use App\Form\Dashboard\AdminParamType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class AdminController extends AbstractController
{
    public $keyValueStore;

    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="dashboard_parameters_admin", path="/parameters/admin", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $form = $this->createForm(AdminParamType::class);

        $keys = $this->keyValueStore->keys();

        foreach ($keys as $key) {
            $form->get($key)->setData($this->keyValueStore->get($key));
        }

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $form_fields = $form->getData();
                foreach ($form_fields as $key => $value) {
                    $this->keyValueStore->set($key, $value);
                }
                $this->addFlash('success', 'Paramètres mis à jour');
            }

            return $this->redirectToRoute('dashboard_parameters_admin');
        }

        return $this->render('Dashboard/Admin/index.html.twig',
            ['form' => $form->createView()]
        );
    }
}

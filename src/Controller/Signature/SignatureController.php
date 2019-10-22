<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Signature;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SignatureController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="OuestINSA_signature_homepage", path="/signature", methods={"GET","HEAD"})
     */
    public function index()
    {
        return $this->render('Signature/index.html.twig');
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="OuestINSA_signature_generate", path="/signature/generate", methods={"GET","HEAD"})
     */
    public function generateAction(){
        return $this->render('Signature/generate.html.twig');
    }
}

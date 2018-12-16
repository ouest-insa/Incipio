<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\User;

use App\Entity\Personne\Personne;
use App\Entity\User\User;
use App\Form\User\UserAdminType;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_user_lister", path="/user/lister", methods={"GET","HEAD"})
     */
    public function listerAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MgateUserBundle:User')->findAll();

        return $this->render('User/Default/lister.html.twig', ['users' => $entities]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_user_modifier", path="/user/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @param Request              $request
     * @param User                 $user
     * @param UserManagerInterface $userManager
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, User $user, UserManagerInterface $userManager)
    {
        $em = $this->getDoctrine()->getManager();

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException('Impossible de modifier un Super Administrateur. Contactez dsi@n7consulting.fr si cette action est vraiment nécessaire.');
        }

        $form = $this->createForm(UserAdminType::class, $user, [
            'user_class' => 'Mgate\UserBundle\Entity\User',
            'roles' => $this->getParameter('security.role_hierarchy.roles'),
        ]);
        $deleteForm = $this->createDeleteForm($user->getId());
        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();

                $userManager->reloadUser($user);
                $this->addFlash('success', 'Utilisateur modifié');

                return $this->redirectToRoute('Mgate_user_lister');
            }
        }

        return $this->render('User/Default/modifier.html.twig', [
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_user_supprimer", path="/user/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @param User    $user    the user to be deleted
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @internal param $id
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user->getId());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                throw new AccessDeniedException('Impossible de supprimer un Super Administrateur. Contactez dsi@n7consulting.fr si cette action est vraiment nécessaire.');
            }

            if ($user->getPersonne()) {
                $user->getPersonne()->setUser(null);
            }
            $user->setPersonne(null);
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé');
        }

        return $this->redirectToRoute('Mgate_user_lister');
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(['id' => $id])
            ->add('id', HiddenType::class)
            ->getForm();
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="Mgate_user_addFromPersonne", path="/user/addFromPersonne/{id}", methods={"POST"})
     *
     * @param Request              $request
     * @param Personne             $personne    the personne whom a user should be added
     * @param UserManagerInterface $userManager
     * @param MailerInterface      $mailer
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function addUserFromPersonneAction(Request $request, Personne $personne, UserManagerInterface $userManager,
                                              MailerInterface $mailer)
    {
        $create_user_form = $this->createFormBuilder(['id' => $personne->getId()])
            ->add('id', HiddenType::class)
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $create_user_form->handleRequest($request);

            if ($create_user_form->isValid()) {
                if ($personne->getUser()) {
                    throw new \Exception('Un utilisateur est déjà liée à cette personne !');
                }
                if (!$personne->getEmail()) {
                    throw new \Exception("l'utilisateur n'a pas d'email valide !");
                }

                $temporaryPassword = md5(mt_rand());
                $token = sha1(uniqid(mt_rand(), true));

                /* Génération de l'user */
                $user = $userManager->createUser();
                $user->setPersonne($personne);
                $user->setEmail($personne->getEmail());
                $user->setPlainPassword($temporaryPassword);
                // Utilisateur à confirmer
                $user->setEnabled(false);
                $user->setConfirmationToken($token);
                $user->setUsername($this->enMinusculeSansAccent($personne->getPrenom() . '.' . $personne->getNom()));

                $userManager->updateUser($user); // Pas besoin de faire un flush (ça le fait tout seul)

                /* Envoie d'un email de confirmation */
                $mailer->sendConfirmationEmailMessage($user);
                $this->addFlash('success', 'Compte utilisateur créé');
            }
        }

        return $this->redirectToRoute('Mgate_user_lister');
    }

    private function enMinusculeSansAccent($texte)
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = str_replace(
            [
                'à', 'â', 'ä', 'á', 'ã', 'å',
                'î', 'ï', 'ì', 'í',
                'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
                'ù', 'û', 'ü', 'ú',
                'é', 'è', 'ê', 'ë',
                'ç', 'ÿ', 'ñ',
            ],
            [
                'a', 'a', 'a', 'a', 'a', 'a',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'e', 'e', 'e', 'e',
                'c', 'y', 'n',
            ],
            $texte
        );

        return $texte;
    }
}

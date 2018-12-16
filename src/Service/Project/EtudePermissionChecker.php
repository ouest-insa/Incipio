<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Project;

use App\Entity\Project\Etude;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EtudePermissionChecker
{
    protected $authorizationChecker;


    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Etude $etude
     * @param User  $user
     *
     * @return bool
     *
     * Comme l'authorizationChecker n'est pas dispo coté twig, on utilisera cette méthode uniquement dans les controllers.
     * Pour twig, utiliser confidentielRefusTwig(Etude, User, is_granted('ROLE_SOUHAITE'))
     */
    public function confidentielRefus(Etude $etude, User $user)
    {
        try {
            if ($etude->getConfidentiel() && !$this->authorizationChecker->isGranted('ROLE_CA')) {
                if ($etude->getSuiveur() && $user->getPersonne()->getId() != $etude->getSuiveur()->getId()) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return true;
        }

        return false;
    }
}

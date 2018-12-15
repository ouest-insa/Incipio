<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// src/Mgate/UserBundle/Entity/User.php

namespace Mgate\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Mgate\PersonneBundle\Entity\Personne;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Mgate\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="\Mgate\PersonneBundle\Entity\Personne", inversedBy="user", cascade={"persist",
     *                                                                     "merge", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $personne;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Groups({"gdpr"})
     *
     * @return array
     */
    public function getJson()
    {
        return [
            'username' => $this->username,
            'usernameCanonical' => $this->usernameCanonical,
            'email' => $this->email,
            'emailCanonical' => $this->emailCanonical,
            'enabled' => $this->enabled,
            'lastLogin' => $this->lastLogin,
            'passwordRequestedAt' => ($this->passwordRequestedAt ?
                $this->passwordRequestedAt->format(\DateTime::ISO8601) : null),
            'roles' => $this->roles,
        ];
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set personne.
     *
     * @param Personne $personne
     *
     * @return User
     */
    public function setPersonne(Personne $personne = null)
    {
        $this->personne = $personne;

        if ($personne) {
            $this->personne->setUser($this);
        }

        return $this;
    }

    /**
     * Get personne.
     *
     * @return Personne
     */
    public function getPersonne()
    {
        return $this->personne;
    }

    private static function convertRoleToLabel($role)
    {
        $roleDisplay = str_replace('ROLE_', '', $role);
        $roleDisplay = str_replace('_', ' ', $roleDisplay);

        return ucwords(strtolower($roleDisplay));
    }

    /** pour afficher les roles
     * Get getRolesDisplay.
     *
     * @return string
     */
    public function getRolesDisplay()
    {
        $rolesArray = $this->getRoles();

        $liste = '';
        foreach ($rolesArray as $role) {
            $liste .= ' ' . self::convertRoleToLabel($role);
        }

        return $liste;
    }
}

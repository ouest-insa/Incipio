<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mgate\SuiviBundle\Entity\DomaineCompetence.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class DomaineCompetence
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Etude", mappedBy="domaineCompetence")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $etude;

    /** nombre de developpeur estimé
     * @var string
     *
     * @ORM\Column(name="nom", type="text", nullable=false)
     */
    private $nom;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->etude = new ArrayCollection();
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return DomaineCompetence
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Get etude.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEtude()
    {
        return $this->etude;
    }

    public function __toString()
    {
        return $this->nom;
    }
}

<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Personne;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("intitule")
 */
class Poste
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="intitule", type="string", length=127)
     */
    private $intitule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Personne\Mandat", mappedBy="poste")
     * @ORM\JoinColumn(nullable=true)
     */
    private $mandats;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

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
     * Get intitule.
     *
     * @return string
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set intitule.
     *
     * @param string $intitule
     *
     * @return Poste
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Poste
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mandat = new ArrayCollection();
    }

    /**
     * Add mandats.
     *
     * @param Mandat $mandats
     *
     * @return Poste
     */
    public function addMandat(Mandat $mandats)
    {
        $this->mandats[] = $mandats;

        return $this;
    }

    /**
     * Remove mandats.
     *
     * @param Mandat $mandats
     */
    public function removeMandat(Mandat $mandats)
    {
        $this->mandats->removeElement($mandats);
    }

    /**
     * Get mandats.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMandats()
    {
        return $this->mandats;
    }

    public function __toString()
    {
        return $this->getIntitule();
    }
}

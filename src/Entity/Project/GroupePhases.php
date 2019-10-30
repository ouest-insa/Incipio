<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Project;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Project\GroupePhasesRepository")
 */
class GroupePhases
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
     * @var Etude
     *
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="groupes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $etude;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var int
     *
     * @ORM\Column(name="numero", type="smallint")
     */
    private $numero;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Phase", mappedBy="groupe", cascade={"persist","remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $phases;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->phases = new ArrayCollection();
    }

    /**
     * @return int amount of child phases
     */
    public function getMontantHT()
    {
        $amount = 0;
        /** @var Phase $p */
        foreach ($this->phases as $p) {
            $amount += $p->getMontantHT();
        }

        return $amount;
    }

    /**
     * @return int JEH number of child phases
     */
    public function getNbrJEH()
    {
        $amount = 0;
        /** @var Phase $p */
        foreach ($this->phases as $p) {
            $amount += $p->getNbrJEH();
        }

        return $amount;
    }

    /** auto-generated methods */

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
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return GroupePhases
     */
    public function setEtude($etude = null)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude.
     *
     * @return Etude
     */
    public function getEtude()
    {
        return $this->etude;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return GroupePhases
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set numero.
     *
     * @param int $numero
     *
     * @return GroupePhases
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero.
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return GroupePhases
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Add phases.
     *
     * @param Phase $phases
     *
     * @return GroupePhases
     */
    public function addPhase(Phase $phases)
    {
        $this->phases[] = $phases;

        return $this;
    }

    /**
     * Remove phases.
     *
     * @param Phase $phases
     */
    public function removePhase(Phase $phases)
    {
        $this->phases->removeElement($phases);
    }

    /**
     * Get phases.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhases()
    {
        return $this->phases;
    }

    public function __toString()
    {
        return 'Groupe : ' . $this->getTitre();
    }
}

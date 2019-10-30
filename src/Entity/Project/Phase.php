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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Project\PhaseRepository")
 */
class Phase
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
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="phases", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $etude;

    /**
     * @ORM\ManyToOne(targetEntity="GroupePhases", inversedBy="phases")
     * @ORM\OrderBy({"numero" = "ASC"})
     */
    private $groupe;

    /**
     * Gedmo\SortablePosition.
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="nbrJEH", type="integer", nullable=true)
     */
    private $nbrJEH;

    /**
     * @var int
     *
     * @ORM\Column(name="prixJEH", type="integer", nullable=true)
     */
    private $prixJEH;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="titre", type="text", nullable=true)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="objectif", type="text", nullable=true)
     */
    private $objectif;

    /**
     * @var string
     *
     * @ORM\Column(name="methodo", type="text", nullable=true)
     */
    private $methodo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDebut", type="datetime", nullable=true)
     */
    private $dateDebut;

    /**
     * @var int
     * @Assert\NotBlank()
     * @ORM\Column(name="delai", type="integer", nullable=true)
     */
    private $delai;

    /**
     * @ORM\ManyToOne(targetEntity="Av", inversedBy="phases")
     */
    private $avenant;

    /**
     * @var int 0 : modifiée, 1:ajoutée -1 : supprimée
     *
     * @ORM\Column(name="etatSurAvenant", type="integer", nullable=true)
     */
    private $etatSurAvenant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project\Mission", inversedBy="phases", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $mission;

    /**
     * ADDITIONAL GETTERS/SETTERS.
     */
    public function getMontantHT()
    {
        return $this->nbrJEH * $this->prixJEH;
    }

    public function getDateFin()
    {
        if ($this->dateDebut) {
            $date = clone $this->dateDebut;
            if (null !== $this->delai) {
                $date->modify(($this->delai > 0 ? '+' : '-') . ' ' . ($this->delai > 0 ? $this->delai : -$this->delai) . ' day');
            }

            return $date;
        } else {
            return new \DateTime('now');
        }
    }

    public function __construct()
    {
        $this->voteCount = 0;
        $this->createdAt = new \DateTime('now');
        $this->prixJEH = 340;
        $this->avenantStatut = 0;
    }

    public function __toString()
    {
        return 'Phase : ' . $this->getTitre();
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
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return Phase
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
     * Set etude.
     *
     * @param GroupePhases $groupe
     *
     * @return Phase
     */
    public function setGroupe($groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe.
     *
     * @return GroupePhases
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set nbrJEH.
     *
     * @param int $nbrJEH
     *
     * @return Phase
     */
    public function setNbrJEH($nbrJEH)
    {
        $this->nbrJEH = $nbrJEH;

        return $this;
    }

    /**
     * Get nbrJEH.
     *
     * @return int
     */
    public function getNbrJEH()
    {
        return $this->nbrJEH;
    }

    /**
     * Set prixJEH.
     *
     * @param int $prixJEH
     *
     * @return Phase
     */
    public function setPrixJEH($prixJEH)
    {
        $this->prixJEH = $prixJEH;

        return $this;
    }

    /**
     * Get prixJEH.
     *
     * @return int
     */
    public function getPrixJEH()
    {
        return $this->prixJEH;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return Phase
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
     * Set objectif.
     *
     * @param string $objectif
     *
     * @return Phase
     */
    public function setObjectif($objectif)
    {
        $this->objectif = $objectif;

        return $this;
    }

    /**
     * Get objectif.
     *
     * @return string
     */
    public function getObjectif()
    {
        return $this->objectif;
    }

    /**
     * Set methodo.
     *
     * @param string $methodo
     *
     * @return Phase
     */
    public function setMethodo($methodo)
    {
        $this->methodo = $methodo;

        return $this;
    }

    /**
     * Get methodo.
     *
     * @return string
     */
    public function getMethodo()
    {
        return $this->methodo;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     *
     * @return Phase
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set delai.
     *
     * @param string $delai
     *
     * @return Phase
     */
    public function setDelai($delai)
    {
        $this->delai = $delai;

        return $this;
    }

    /**
     * Get delai.
     *
     * @return string
     */
    public function getDelai()
    {
        return $this->delai;
    }

    /**
     * Set position.
     *
     * @param string $position
     *
     * @return Phase
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set avenant.
     *
     * @param Av $avenant
     *
     * @return Phase
     */
    public function setAvenant(Av $avenant = null)
    {
        $this->avenant = $avenant;

        return $this;
    }

    /**
     * Get avenant.
     *
     * @return Av
     */
    public function getAvenant()
    {
        return $this->avenant;
    }

    public static function getEtatSurAvenantChoice()
    {
        return [0 => 'Modifiée', //Inutile
            1 => 'Ajoutée',
            -1 => 'Supprimée',
        ];
    }

    public static function getEtatSurAvenantChoiceAssert()
    {
        return array_keys(self::getEtatSurAvenantChoice());
    }

    /**
     * Set etatSurAvenant.
     *
     * @param int $etatSurAvenant
     *
     * @return Phase
     */
    public function setEtatSurAvenant($etatSurAvenant)
    {
        $this->etatSurAvenant = $etatSurAvenant;

        return $this;
    }

    /**
     * Get etatSurAvenant.
     *
     * @return int
     */
    public function getEtatSurAvenant()
    {
        return $this->etatSurAvenant;
    }

    /**
     * @return mixed
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * @param mixed $mission
     */
    public function setMission($mission)
    {
        $this->mission = $mission;
    }
}

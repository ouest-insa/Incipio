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
 * @ORM\Entity
 */
class AvMission extends DocType
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
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="avMissions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $etude;

    /**
     * @var Mission
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project\Mission")
     */
    private $mission;

    /**
     * @var int
     * @ORM\OneToMany(targetEntity="App\Entity\Project\RepartitionJEH", mappedBy="avMission", cascade={"persist","remove"})
     */
    private $nouvelleRepartition;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="nouveauPourcentage", type="integer")
     */
    private $nouveauPourcentage;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="differentielDelai", type="integer")
     */
    private $differentielDelai;

    /**
     * @var Av
     *
     * @ORM\ManyToOne(targetEntity="Av", inversedBy="avenantsMissions")
     */
    private $avenant;

    public function __toString()
    {
        return 'AvMission ' . $this->id;
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
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->nouvelleRepartition = new ArrayCollection();
    }

    /**
     * Set differentielDelai.
     *
     * @param int $differentielDelai
     *
     * @return AvMission
     */
    public function setDifferentielDelai($differentielDelai)
    {
        $this->differentielDelai = $differentielDelai;

        return $this;
    }

    /**
     * Get differentielDelai.
     *
     * @return int
     */
    public function getDifferentielDelai()
    {
        return $this->differentielDelai;
    }

    /**
     * Add nouvelleRepartition.
     *
     * @param RepartitionJEH $nouvelleRepartition
     *
     * @return AvMission
     */
    public function addNouvelleRepartition(RepartitionJEH $nouvelleRepartition)
    {
        $this->nouvelleRepartition[] = $nouvelleRepartition;

        return $this;
    }

    /**
     * Remove nouvelleRepartition.
     *
     * @param RepartitionJEH $nouvelleRepartition
     */
    public function removeNouvelleRepartition(RepartitionJEH $nouvelleRepartition)
    {
        $this->nouvelleRepartition->removeElement($nouvelleRepartition);
    }

    /**
     * Get nouvelleRepartition.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNouvelleRepartition()
    {
        return $this->nouvelleRepartition;
    }

    /**
     * Set mission.
     *
     * @param Mission $mission
     *
     * @return AvMission
     */
    public function setMission(Mission $mission = null)
    {
        $this->mission = $mission;

        return $this;
    }

    /**
     * Get mission.
     *
     * @return Mission
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * Set nouveauPourcentage.
     *
     * @param int $nouveauPourcentage
     *
     * @return AvMission
     */
    public function setNouveauPourcentage($nouveauPourcentage)
    {
        $this->nouveauPourcentage = $nouveauPourcentage;

        return $this;
    }

    /**
     * Get nouveauPourcentage.
     *
     * @return int
     */
    public function getNouveauPourcentage()
    {
        return $this->nouveauPourcentage;
    }

    /**
     * Set avenant.
     *
     * @param Av $avenant
     *
     * @return AvMission
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

    /**
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return AvMission
     */
    public function setEtude(Etude $etude)
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
}

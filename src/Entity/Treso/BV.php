<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mgate\SuiviBundle\Entity\Mission;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"mandat", "numero"},
 *     errorPath="numero",
 *     message="Le couple mandat/numéro doit être unique")
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"mandat", "numero"})})
 * @ORM\Entity(repositoryClass="Mgate\TresoBundle\Entity\BVRepository")
 */
class BV
{
    // Liée au répartition JEH laisser le choix d'ajouter des existantes (une fois que les avenants seront OK)
    // si plusieur repartition, moyenner

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32767)
     *
     * @ORM\Column(name="mandat", type="smallint")
     */
    private $mandat;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32767)
     *
     * @ORM\Column(name="numero", type="smallint")
     */
    private $numero;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32767)
     *
     * @ORM\Column(name="nombreJEH", type="smallint")
     */
    private $nombreJEH;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="remunerationBruteParJEH", type="float")
     */
    private $remunerationBruteParJEH;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="dateDeVersement", type="date")
     */
    private $dateDeVersement;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="dateDemission", type="date")
     */
    private $dateDemission;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="typeDeTravail", type="string", length=255)
     */
    private $typeDeTravail;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="numeroVirement", type="string", length=255)
     */
    private $numeroVirement;

    /**
     * @ORM\ManyToOne(targetEntity="Mgate\SuiviBundle\Entity\Mission")
     */
    private $mission;

    /**
     * @var BaseURSSAF
     *
     * @ORM\ManyToOne(targetEntity="Mgate\TresoBundle\Entity\BaseURSSAF")
     */
    private $baseURSSAF;

    /**
     * @ORM\ManyToMany(targetEntity="Mgate\TresoBundle\Entity\CotisationURSSAF")
     */
    private $cotisationURSSAF;

    //GETTER ADITION
    public function getReference()
    {
        return $this->mandat . '-BV-' . sprintf('%1$02d', $this->numero);
    }

    public function getRemunerationBrute()
    {
        return $this->getRemunerationBruteParJEH() * $this->nombreJEH;
    }

    public function getAssietteDesCotisations()
    {
        return $this->baseURSSAF ? $this->baseURSSAF->getBaseURSSAF() * $this->nombreJEH : null;
    }

    public function getRemunerationNet()
    {
        return $this->getRemunerationBrute() - $this->getPartEtudiant();
    }

    public function getRemunerationNetImposable()
    {
        $result = $this->getRemunerationNet();

        foreach ($this->cotisationURSSAF as $cotisation) {
            if ('C.R.D.S. + CSG non déductible' == $cotisation->getLibelle()) {
                $result += $this->getAssietteDesCotisations() * $cotisation->getTauxPartEtu();
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTauxPartJunior()
    {
        $tauxPartJunior = [
            'baseURSSAF' => 0,
            'baseBrute' => 0,
        ];

        foreach ($this->cotisationURSSAF as $cotisation) {
            if ($cotisation->getSurBaseURSSAF() && $this->baseURSSAF) {
                $tauxPartJunior['baseURSSAF'] += $cotisation->getTauxPartJE();
            } else {
                $tauxPartJunior['baseBrute'] += $cotisation->getTauxPartJE();
            }
        }

        return $tauxPartJunior;
    }

    /**
     * @return array
     */
    public function getTauxPartEtu()
    {
        $tauxPartEtu = [
            'baseURSSAF' => 0,
            'baseBrute' => 0,
        ];

        foreach ($this->cotisationURSSAF as $cotisation) {
            if ($cotisation->getSurBaseURSSAF() && $this->baseURSSAF) {
                $tauxPartEtu['baseURSSAF'] += $cotisation->getTauxPartEtu();
            } else {
                $tauxPartEtu['baseBrute'] += $cotisation->getTauxPartEtu();
            }
        }

        return $tauxPartEtu;
    }

    /**
     * @param bool $inArray
     *
     * @return mixed
     */
    public function getPartJunior($inArray = false)
    {
        $partJunior = [
            'baseURSSAF' => 0,
            'baseBrute' => 0,
        ];
        foreach ($this->cotisationURSSAF as $cotisation) {
            if ($cotisation->getSurBaseURSSAF() && $this->baseURSSAF) {
                $partJunior['baseURSSAF'] += round($this->nombreJEH * $this->baseURSSAF->getBaseURSSAF() * $cotisation->getTauxPartJE(), 2);
            } else {
                $partJunior['baseBrute'] += round($this->nombreJEH * $cotisation->getTauxPartJE() * $this->remunerationBruteParJEH, 2);
            }
        }

        return $inArray ? $partJunior : $partJunior['baseURSSAF'] + $partJunior['baseBrute'];
    }

    /**
     * @param bool $inArray
     * @param bool $nonImposable
     *
     * @return mixed
     */
    public function getPartEtudiant($inArray = false, $nonImposable = false)
    {
        $partEtu = [
            'baseURSSAF' => 0,
            'baseBrute' => 0,
        ];

        foreach ($this->cotisationURSSAF as $cotisation) {
            if ($nonImposable && !$cotisation->getDeductible()) {
                continue;
            }

            if ($cotisation->getSurBaseURSSAF() && $this->baseURSSAF) {
                $partEtu['baseURSSAF'] += round($this->nombreJEH * $this->baseURSSAF->getBaseURSSAF() * $cotisation->getTauxPartEtu(), 2);
            } else {
                $partEtu['baseBrute'] += round($this->nombreJEH * $cotisation->getTauxPartEtu() * $this->remunerationBruteParJEH, 2);
            }
        }

        return $inArray ? $partEtu : $partEtu['baseURSSAF'] + $partEtu['baseBrute'];
    }

    ///////

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cotisationURSSAF = new ArrayCollection();
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
     * Set mandat.
     *
     * @param int $mandat
     *
     * @return BV
     */
    public function setMandat($mandat)
    {
        $this->mandat = $mandat;

        return $this;
    }

    /**
     * Get mandat.
     *
     * @return int
     */
    public function getMandat()
    {
        return $this->mandat;
    }

    /**
     * Set numero.
     *
     * @param int $numero
     *
     * @return BV
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
     * Set nombreJEH.
     *
     * @param int $nombreJEH
     *
     * @return BV
     */
    public function setNombreJEH($nombreJEH)
    {
        $this->nombreJEH = $nombreJEH;

        return $this;
    }

    /**
     * Get nombreJEH.
     *
     * @return int
     */
    public function getNombreJEH()
    {
        return $this->nombreJEH;
    }

    /**
     * Set remunerationBruteParJEH.
     *
     * @param float $remunerationBruteParJEH
     *
     * @return BV
     */
    public function setRemunerationBruteParJEH($remunerationBruteParJEH)
    {
        $this->remunerationBruteParJEH = $remunerationBruteParJEH;

        return $this;
    }

    /**
     * Get remunerationBruteParJEH.
     *
     * @return float
     */
    public function getRemunerationBruteParJEH()
    {
        return $this->remunerationBruteParJEH;
    }

    /**
     * Set dateDeVersement.
     *
     * @param \DateTime $dateDeVersement
     *
     * @return BV
     */
    public function setDateDeVersement($dateDeVersement)
    {
        $this->dateDeVersement = $dateDeVersement;

        return $this;
    }

    /**
     * Get dateDeVersement.
     *
     * @return \DateTime
     */
    public function getDateDeVersement()
    {
        return $this->dateDeVersement;
    }

    /**
     * Set dateDemission.
     *
     * @param \DateTime $dateDemission
     *
     * @return BV
     */
    public function setDateDemission($dateDemission)
    {
        $this->dateDemission = $dateDemission;

        return $this;
    }

    /**
     * Get dateDemission.
     *
     * @return \DateTime
     */
    public function getDateDemission()
    {
        return $this->dateDemission;
    }

    /**
     * Set typeDeTravail.
     *
     * @param string $typeDeTravail
     *
     * @return BV
     */
    public function setTypeDeTravail($typeDeTravail)
    {
        $this->typeDeTravail = $typeDeTravail;

        return $this;
    }

    /**
     * Get typeDeTravail.
     *
     * @return string
     */
    public function getTypeDeTravail()
    {
        return $this->typeDeTravail;
    }

    /**
     * Set numeroVirement.
     *
     * @param string $numeroVirement
     *
     * @return BV
     */
    public function setNumeroVirement($numeroVirement)
    {
        $this->numeroVirement = $numeroVirement;

        return $this;
    }

    /**
     * Get numeroVirement.
     *
     * @return string
     */
    public function getNumeroVirement()
    {
        return $this->numeroVirement;
    }

    /**
     * Set mission.
     *
     * @param Mission $mission
     *
     * @return BV
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
     * Set baseURSSAF.
     *
     * @param BaseURSSAF $baseURSSAF
     *
     * @return BV
     */
    public function setBaseURSSAF(BaseURSSAF $baseURSSAF = null)
    {
        $this->baseURSSAF = $baseURSSAF;

        return $this;
    }

    /**
     * Get baseURSSAF.
     *
     * @return BaseURSSAF
     */
    public function getBaseURSSAF()
    {
        return $this->baseURSSAF;
    }

    /**
     * Add cotisationURSSAF.
     *
     * @param CotisationURSSAF $cotisationURSSAF
     *
     * @return BV
     */
    public function addCotisationURSSAF(CotisationURSSAF $cotisationURSSAF)
    {
        $this->cotisationURSSAF[] = $cotisationURSSAF;

        return $this;
    }

    /**
     * Remove cotisationURSSAF.
     *
     * @param CotisationURSSAF $cotisationURSSAF
     */
    public function removeCotisationURSSAF(CotisationURSSAF $cotisationURSSAF)
    {
        $this->cotisationURSSAF->removeElement($cotisationURSSAF);
    }

    /**
     * Get cotisationURSSAF.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCotisationURSSAF()
    {
        return $this->cotisationURSSAF;
    }

    /**
     * Get cotisationURSSAF.
     *
     * @return BV
     */
    public function setCotisationURSSAF()
    {
        $this->cotisationURSSAF = null;

        return $this;
    }
}

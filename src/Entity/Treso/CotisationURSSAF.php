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

use Doctrine\ORM\Mapping as ORM;

/**
 * CotisationURSSAF.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mgate\TresoBundle\Entity\CotisationURSSAFRepository")
 */
class CotisationURSSAF
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
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSurBaseURSSAF", type="boolean")
     * keep compatibility with former name of that property.
     */
    private $surBaseURSSAF;

    /**
     * @var string
     *
     * @ORM\Column(name="tauxPartJE", type="decimal", scale=5)
     */
    private $tauxPartJE;

    /**
     * @var string
     *
     * @ORM\Column(name="tauxPartEtu", type="decimal", scale=5)
     */
    private $tauxPartEtu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDebut", type="date")
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFin", type="date")
     */
    private $dateFin;

    /**
     * @var bool
     *
     * @ORM\Column(name="deductible", type="boolean")
     */
    private $deductible;

    public function __construct()
    {
        $this->deductible = true;

        return $this;
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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return CotisationURSSAF
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set SurBaseURSSAF.
     *
     * @param bool $SurBaseURSSAF
     *
     * @return CotisationURSSAF
     */
    public function setSurBaseURSSAF($surBaseURSSAF)
    {
        $this->surBaseURSSAF = $surBaseURSSAF;

        return $this;
    }

    /**
     * Get SurBaseURSSAF.
     *
     * @return bool
     */
    public function getSurBaseURSSAF()
    {
        return $this->surBaseURSSAF;
    }

    /**
     * Set tauxPartJE.
     *
     * @param string $tauxPartJE
     *
     * @return CotisationURSSAF
     */
    public function setTauxPartJE($tauxPartJE)
    {
        $this->tauxPartJE = $tauxPartJE;

        return $this;
    }

    /**
     * Get tauxPartJE.
     *
     * @return string
     */
    public function getTauxPartJE()
    {
        return $this->tauxPartJE;
    }

    /**
     * Set tauxPartEtu.
     *
     * @param string $tauxPartEtu
     *
     * @return CotisationURSSAF
     */
    public function setTauxPartEtu($tauxPartEtu)
    {
        $this->tauxPartEtu = $tauxPartEtu;

        return $this;
    }

    /**
     * Get tauxPartEtu.
     *
     * @return string
     */
    public function getTauxPartEtu()
    {
        return $this->tauxPartEtu;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     *
     * @return CotisationURSSAF
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
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     *
     * @return CotisationURSSAF
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set deductible.
     *
     * @param bool $deductible
     *
     * @return CotisationURSSAF
     */
    public function setDeductible($deductible)
    {
        $this->deductible = $deductible;

        return $this;
    }

    /**
     * Get deductible.
     *
     * @return bool
     */
    public function getDeductible()
    {
        return $this->deductible;
    }
}

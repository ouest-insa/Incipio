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
use Mgate\PersonneBundle\Entity\Personne;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"mandat", "numero"},
 *     errorPath="numero",
 *     message="Le couple mandat/numéro doit être unique")
 *
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"mandat", "numero"})})
 * @ORM\Entity(repositoryClass="Mgate\TresoBundle\Entity\NoteDeFraisRepository")
 */
class NoteDeFrais implements TresoDetailableInterface
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
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="date", type="date",nullable=false)
     */
    private $date;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="mandat", type="integer", nullable=false)
     */
    private $mandat;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="numero", type="integer", nullable=false)
     */
    private $numero;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="objet", type="text", nullable=false)
     */
    private $objet;

    /**
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="NoteDeFraisDetail", mappedBy="noteDeFrais", cascade={"persist", "detach", "remove"}, orphanRemoval=true)
     */
    private $details;

    /**
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Mgate\PersonneBundle\Entity\Personne")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $demandeur;

    /**
     * ADDITIONAL GETTERS.
     */
    public function getMontantHT()
    {
        $montantHT = 0;
        foreach ($this->details as $detail) {
            $montantHT += $detail->getMontantHT();
        }

        return $montantHT;
    }

    public function getMontantTVA()
    {
        $TVA = 0;
        foreach ($this->details as $detail) {
            $TVA += $detail->getMontantTVA();
        }

        return $TVA;
    }

    public function getMontantTTC()
    {
        return $this->getMontantHT() + $this->getMontantTVA();
    }

    public function getReference()
    {
        // UNSAFE
        return $this->mandat . '-NF' . $this->getNumero() . '-' . $this->getDemandeur()->getMembre()->getIdentifiant();
    }

    /*
     * STANDARDS GETTERS/SETTERS
     */

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
        $this->details = new ArrayCollection();
    }

    /**
     * Set objet.
     *
     * @param string $objet
     *
     * @return NoteDeFrais
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet.
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Add details.
     *
     * @param NoteDeFraisDetail $details
     *
     * @return NoteDeFrais
     */
    public function addDetail(NoteDeFraisDetail $details)
    {
        $this->details[] = $details;

        return $this;
    }

    /**
     * Remove details.
     *
     * @param NoteDeFraisDetail $details
     */
    public function removeDetail(NoteDeFraisDetail $details)
    {
        $this->details->removeElement($details);
        $details->setNoteDeFrais();
    }

    /**
     * Get details.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set demandeur.
     *
     * @param Personne $demandeur
     *
     * @return NoteDeFrais
     */
    public function setDemandeur(Personne $demandeur = null)
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    /**
     * Get demandeur.
     *
     * @return Personne
     */
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set mandat.
     *
     * @param int $mandat
     *
     * @return NoteDeFrais
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
     * @return NoteDeFrais
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return NoteDeFrais
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

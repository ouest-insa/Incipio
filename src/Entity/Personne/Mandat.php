<?php
/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PersonneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mandat.
 * Table intermédiaire ManyToMany avec attribut : Mandat = MembrePoste.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mgate\PersonneBundle\Entity\MandatRepository")
 */
class Mandat
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
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @Groups({"gdpr"})
     *
     * @ORM\Column(name="debutMandat", type="date",nullable=false)
     */
    private $debutMandat;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @Groups({"gdpr"})
     *
     * @ORM\Column(name="finMandat", type="date",nullable=false)
     */
    private $finMandat;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Mgate\PersonneBundle\Entity\Membre", inversedBy="mandats")
     */
    private $membre;

    /**
     * @var Poste
     *
     * @Assert\NotNull()
     *
     * @Groups({"gdpr"})
     *
     * @ORM\ManyToOne(targetEntity="Mgate\PersonneBundle\Entity\Poste", inversedBy="mandats")
     */
    private $poste;

    public function __toString()
    {
        return 'Mandat ' . $this->getDebutMandat()->format('d/m/Y') . ' - ' . $this->getFinMandat()->format('d/m/Y');
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
     * Set debutMandat.
     *
     * @param \DateTime $debutMandat
     *
     * @return Mandat
     */
    public function setDebutMandat($debutMandat)
    {
        $this->debutMandat = $debutMandat;

        return $this;
    }

    /**
     * Get debutMandat.
     *
     * @return \DateTime
     */
    public function getDebutMandat()
    {
        return $this->debutMandat;
    }

    /**
     * Set finMandat.
     *
     * @param \DateTime $finMandat
     *
     * @return Mandat
     */
    public function setFinMandat($finMandat)
    {
        $this->finMandat = $finMandat;

        return $this;
    }

    /**
     * Get finMandat.
     *
     * @return \DateTime
     */
    public function getFinMandat()
    {
        return $this->finMandat;
    }

    /**
     * Set membre.
     *
     * @param Membre $membre
     *
     * @return Mandat
     */
    public function setMembre(Membre $membre)
    {
        $this->membre = $membre;

        return $this;
    }

    /**
     * Get membre.
     *
     * @return Membre
     */
    public function getMembre()
    {
        return $this->membre;
    }

    /**
     * Set poste.
     *
     * @param Poste $poste
     *
     * @return Mandat
     */
    public function setPoste(Poste $poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste.
     *
     * @return Poste
     */
    public function getPoste()
    {
        return $this->poste;
    }
}

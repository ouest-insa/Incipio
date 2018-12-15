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
 * @ORM\Table()
 * @ORM\Entity
 */
class Employe implements AnonymizableInterface
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
     * @var Prospect
     *
     * @Assert\NotNull()
     *
     * @Groups({"gdpr"})
     *
     * @ORM\ManyToOne(targetEntity="Prospect", inversedBy="employes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $prospect;

    /**
     * @var Personne
     *
     * @Assert\Valid()
     * @Assert\NotNull()
     *
     * @ORM\OneToOne(targetEntity="Personne", inversedBy="employe", fetch="EAGER", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $personne;

    /**
     * @var string
     *
     * @Groups({"gdpr"})
     *
     * @ORM\Column(name="poste", type="string", length=255, nullable=true)
     */
    private $poste;

    /**
     * {@inheritdoc}
     */
    public function anonymize(): void
    {
        $this->poste = null;
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
     * Set prospect.
     *
     * @param Prospect $prospect
     *
     * @return Employe
     */
    public function setProspect(Prospect $prospect)
    {
        $this->prospect = $prospect;

        return $this;
    }

    /**
     * @return Prospect
     */
    public function getProspect()
    {
        return $this->prospect;
    }

    /**
     * Set personne.
     *
     * @param Personne $personne
     *
     * @return Employe
     */
    public function setPersonne(Personne $personne)
    {
        $personne->setEmploye($this);

        $this->personne = $personne;

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

    /**
     * Set poste.
     *
     * @param string $poste
     *
     * @return Employe
     */
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste.
     *
     * @return string
     */
    public function getPoste()
    {
        return $this->poste;
    }

    public function __toString()
    {
        return $this->getPersonne()->getPrenom() . ' ' . $this->getPersonne()->getNom();
    }
}

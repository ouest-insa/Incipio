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
use Mgate\PersonneBundle\Entity\Prospect;
use Mgate\PubliBundle\Controller\TraitementController;
use Mgate\SuiviBundle\Entity\Etude;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mgate\TresoBundle\Entity\FactureRepository")
 */
class Facture implements TresoDetailableInterface
{
    const TYPE_ACHAT = 1;

    const TYPE_VENTE = 2;

    const TYPE_VENTE_ACCOMPTE = 3;

    const TYPE_VENTE_INTERMEDIAIRE = 4;

    const TYPE_VENTE_SOLDE = 5;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mgate\SuiviBundle\Entity\Etude", inversedBy="factures",  cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $etude;

    /**
     * @Assert\NotNull()
     *
     * @ORM\ManyToOne(targetEntity="Mgate\PersonneBundle\Entity\Prospect", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $beneficiaire;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(32767)
     *
     * @ORM\Column(name="exercice", type="smallint")
     */
    private $exercice;

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
     * @abstract 1 is Achat, > 2 is vente
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(1)
     * @Assert\LessThanOrEqual(5)
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="dateEmission", type="date", nullable=false)
     */
    private $dateEmission;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateVersement", type="date", nullable=true)
     */
    private $dateVersement;

    /**
     * @Assert\Valid()
     * @ORM\OneToMany(targetEntity="FactureDetail", mappedBy="facture", cascade="all", orphanRemoval=true)
     */
    private $details;

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
     * @ORM\OneToOne(targetEntity="FactureDetail", mappedBy="factureADeduire", cascade="all", orphanRemoval=true)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $montantADeduire;

    /**
     * ADDITIONNAL.
     */

    /*
     * pour la TVA collectée (factures clients), la date d’exigibilité c’est la date d’encaissement
     * pour la TVA déductible, la date d’exigibilité c’est soit la date de facturation dans le cas de vente de biens soit la date de décaissement dans le cas de services
     * la CNJE simplifie pour les Junior-Entrepreneurs en leur disant de prendre en compte la date de facturation pour toutes les opérations (biens et services)
     */
    public function getDate()
    {
        return self::TYPE_ACHAT == $this->type ? $this->dateEmission : $this->dateVersement;
    }

    public function getReference()
    {
        return $this->exercice . '-' . ($this->type > 1 ? 'FV' : 'FA') . '-' . sprintf('%1$02d', $this->numero);
    }

    public function getMontantHT()
    {
        $montantHT = 0;
        foreach ($this->details as $detail) {
            $montantHT += $detail->getMontantHT();
        }
        if ($this->montantADeduire) {
            $montantHT -= $this->montantADeduire->getMontantHT();
        }

        return $montantHT;
    }

    public function getMontantTVA()
    {
        $TVA = 0;
        foreach ($this->details as $detail) {
            $TVA += $detail->getMontantHT() * $detail->getTauxTVA() / 100;
        }
        if ($this->montantADeduire) {
            $TVA -= $this->montantADeduire->getTauxTVA() * $this->montantADeduire->getMontantHT() / 100;
        }

        return $TVA;
    }

    public function getMontantTTC()
    {
        return $this->getMontantHT() + $this->getMontantTVA();
    }

    public function getTypeAbbrToString()
    {
        $type = [
            0 => 'Facture',
            1 => 'Facture',
            2 => 'FV',
            3 => TraitementController::DOCTYPE_FACTURE_ACOMTE,
            4 => TraitementController::DOCTYPE_FACTURE_INTERMEDIAIRE,
            5 => TraitementController::DOCTYPE_FACTURE_SOLDE, ];

        return $type[$this->type];
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getTypeToString()
    {
        $type = $this->getTypeChoices();

        return $type[$this->type];
    }

    public static function getTypeChoices()
    {
        return [
            self::TYPE_ACHAT => 'FA - Facture d\'achat',
            self::TYPE_VENTE => 'FV - Facture de vente',
            self::TYPE_VENTE_ACCOMPTE => 'FV - Facture d\'acompte',
            self::TYPE_VENTE_INTERMEDIAIRE => 'FV - Facture intermédiaire',
            self::TYPE_VENTE_SOLDE => 'FV - Facture de solde',
            ];
    }

    /**
     * STANDARDS GETTER / SETTER.
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
        $this->montantADeduire = new FactureDetail();
        $this->montantADeduire->setMontantHT(0);
    }

    /**
     * Set exercice.
     *
     * @param int $exercice
     *
     * @return Facture
     */
    public function setExercice($exercice)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice.
     *
     * @return int
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set numero.
     *
     * @param int $numero
     *
     * @return Facture
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
     * Set type.
     *
     * @param int $type
     *
     * @return Facture
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set dateEmission.
     *
     * @param \DateTime $dateEmission
     *
     * @return Facture
     */
    public function setDateEmission($dateEmission)
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }

    /**
     * Get dateEmission.
     *
     * @return \DateTime
     */
    public function getDateEmission()
    {
        return $this->dateEmission;
    }

    /**
     * Set dateVersement.
     *
     * @param \DateTime $dateVersement
     *
     * @return Facture
     */
    public function setDateVersement($dateVersement)
    {
        $this->dateVersement = $dateVersement;

        return $this;
    }

    /**
     * Get dateVersement.
     *
     * @return \DateTime
     */
    public function getDateVersement()
    {
        return $this->dateVersement;
    }

    /**
     * Add details.
     *
     * @param FactureDetail $details
     *
     * @return Facture
     */
    public function addDetail(FactureDetail $details)
    {
        $this->details[] = $details;

        return $this;
    }

    /**
     * Remove details.
     *
     * @param FactureDetail $details
     */
    public function removeDetail(FactureDetail $details)
    {
        $this->details->removeElement($details);
        $details->setFacture();
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
     * Set objet.
     *
     * @param string $objet
     *
     * @return Facture
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
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return Facture
     */
    public function setEtude(Etude $etude = null)
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
     * Set montantADeduire.
     *
     * @param FactureDetail $montantADeduire
     *
     * @return Facture
     */
    public function setMontantADeduire(FactureDetail $montantADeduire = null)
    {
        $this->montantADeduire = $montantADeduire;

        return $this;
    }

    /**
     * Get montantADeduire.
     *
     * @return FactureDetail
     */
    public function getMontantADeduire()
    {
        return $this->montantADeduire;
    }

    /**
     * Set beneficiaire.
     *
     * @param Prospect $beneficiaire
     *
     * @return Facture
     */
    public function setBeneficiaire(Prospect $beneficiaire)
    {
        $this->beneficiaire = $beneficiaire;

        return $this;
    }

    /**
     * Get beneficiaire.
     *
     * @return Prospect
     */
    public function getBeneficiaire()
    {
        return $this->beneficiaire;
    }

    public function __toString()
    {
        return $this->getNumero() . ' ' . $this->getObjet();
    }
}

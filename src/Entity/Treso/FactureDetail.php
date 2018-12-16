<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Treso;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class FactureDetail implements TresoDetailInterface
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
     * @ORM\ManyToOne(targetEntity="Facture", inversedBy="details")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $facture;

    /**
     * Previously, a FactureDetail was linked to a facture through the Facture attribute. However, a montantADeduire is
     * also a FactureDetail and was also linked to a Facture using that field. As a result, when Facture->getDetails
     * was called, the ORM was executing a SQL query such as `Select * from FactureDetail where facture_id = :id`. This
     * query was also retrieving the montantADeduire, leading to wrong factures, with for instance montantADeduire
     * considered as a detail and as montantADeduire.
     * This field introduces a second way to link a FactureDetail to a Facture and removes the previous bug:
     * montantADeduire have their attribute facture set to null. In addition, A consistence constraint checks that a
     * FactureDetail has factureADeduire xor Facture field set.
     *
     * @ORM\OneToOne(targetEntity="Facture", inversedBy="montantADeduire")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $factureADeduire;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="montantHT", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $montantHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tauxTVA", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $tauxTVA;

    /**
     * @ORM\ManyToOne(targetEntity="Compte")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compte;

    /**
     * ADDITIONAL.
     */

    /**
     * Refer to comment on factureADeduire for more details.
     *
     * @Assert\IsTrue(message="Un FactureDetail ne peut avoir en même temps une facture et une facture à déduire")
     */
    public function isConsistent()
    {
        return $this->facture xor $this->factureADeduire ||
            // Can be met on new FactureDetails which have none of these set yet.
            (!$this->facture && !$this->factureADeduire);
    }

    public function getMontantTVA()
    {
        return $this->tauxTVA * $this->montantHT / 100;
    }

    public function getMontantTTC()
    {
        return $this->montantHT + $this->getMontantTVA();
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
     * Set description.
     *
     * @param string $description
     *
     * @return FactureDetail
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
     * Set montantHT.
     *
     * @param float $montantHT
     *
     * @return FactureDetail
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;

        return $this;
    }

    /**
     * Get montantHT.
     *
     * @return float
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set tauxTVA.
     *
     * @param float $tauxTVA
     *
     * @return FactureDetail
     */
    public function setTauxTVA($tauxTVA)
    {
        $this->tauxTVA = $tauxTVA;

        return $this;
    }

    /**
     * Get tauxTVA.
     *
     * @return float
     */
    public function getTauxTVA()
    {
        return $this->tauxTVA;
    }

    /**
     * Set compte.
     *
     * @param Compte $compte
     *
     * @return FactureDetail
     */
    public function setCompte(Compte $compte = null)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte.
     *
     * @return Compte
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set facture.
     *
     * @param Facture $facture
     *
     * @return FactureDetail
     */
    public function setFacture(Facture $facture = null)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get facture.
     *
     * @return Facture|null
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * @return Facture|null
     */
    public function getFactureADeduire()
    {
        return $this->factureADeduire;
    }

    /**
     * @param Facture $factureADeduire
     *
     * @return FactureDetail
     */
    public function setFactureADeduire(Facture $factureADeduire)
    {
        $this->factureADeduire = $factureADeduire;

        return $this;
    }
}

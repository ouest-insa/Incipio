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
class NoteDeFraisDetail implements TresoDetailInterface {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="NoteDeFrais", inversedBy="details", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $noteDeFrais;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = "";

    /**
     * @var float
     *
     * @ORM\Column(name="prixHT", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $prixHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tauxTVA", type="decimal", precision=6, scale=2, nullable=true, options={"default" : 20})
     */
    private $tauxTVA;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="kilometrage", type="integer", nullable=true)
     */
    private $kilometrage;

    /**
     * @var float
     *
     * @ORM\Column(name="tauxKm", type="integer", nullable=true, options={"default" : 14})
     */
    private $tauxKm;

    /**
     * @var float
     *
     * @ORM\Column(name="peageHT", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $peageHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tvaPeages", type="decimal", precision=6, scale=2, nullable=true, options={"default" : 20})
     */
    private $tvaPeages;

    /**
     * @ORM\ManyToOne(targetEntity="Compte")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compte;

    //categorie à ajouter via ManytoMany compteComptable

    // Perso
    public static function getTypeChoices()
    {
        return [
            1 => 'Classique',
            2 => 'Kilométrique'
        ];
    }

    public function getMontantHT()
    {
        if ($this->type == 1) {
            return $this->prixHT;
        } elseif ($this->type == 2) {
            return ($this->kilometrage * ($this->tauxKm / 100)) + $this->peageHT;
        } else {
            return 0;
        }
    }

    public function getMontantTVA()
    {
        if ($this->type == 1) {
            return $this->tauxTVA * $this->getMontantHT() / 100;
        } elseif ($this->type == 2) {
            return ($this->tvaPeages / 100) * $this->peageHT;
        } else {
            return 0;
        }
    }

    public function getMontantTTC()
    {
        return $this->getMontantHT() + $this->getMontantTVA();
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
     * @return NoteDeFraisDetail
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
     * Set prixHT.
     *
     * @param float $prixHT
     *
     * @return NoteDeFraisDetail
     */
    public function setPrixHT($prixHT)
    {
        $this->prixHT = $prixHT;
        return $this;
    }

    /**
     * Get prixHT.
     *
     * @return float
     */
    public function getPrixHT()
    {
        return $this->prixHT;
    }

    /**
     * Set peageHT.
     *
     * @param float $peageHT
     *
     * @return NoteDeFraisDetail
     */
    public function setPeageHT($peageHT)
    {
        $this->peageHT = $peageHT;
        return $this;
    }

    /**
     * Get peageHT.
     *
     * @return float
     */
    public function getPeageHT()
    {
        return $this->peageHT;
    }

    /**
     * Set tauxTVA.
     *
     * @param float $tauxTVA
     *
     * @return NoteDeFraisDetail
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
     * Set type.
     *
     * @param int $type
     *
     * @return NoteDeFraisDetail
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
     * Set kilometrage.
     *
     * @param int $kilometrage
     *
     * @return NoteDeFraisDetail
     */
    public function setKilometrage($kilometrage)
    {
        $this->kilometrage = $kilometrage;
        return $this;
    }

    /**
     * Get kilometrage.
     *
     * @return int
     */
    public function getKilometrage()
    {
        return $this->kilometrage;
    }

    /**
     * Set tauxKm.
     *
     * @param float $tauxKm
     *
     * @return NoteDeFraisDetail
     */
    public function setTauxKm($tauxKm)
    {
        $this->tauxKm = $tauxKm;
        return $this;
    }

    /**
     * Get tauxKm.
     *
     * @return float
     */
    public function getTauxKm()
    {
        return $this->tauxKm;
    }

    /**
     * Set noteDeFrais.
     *
     * @param NoteDeFrais $noteDeFrais
     *
     * @return NoteDeFraisDetail
     */
    public function setNoteDeFrais(NoteDeFrais $noteDeFrais = null)
    {
        $this->noteDeFrais = $noteDeFrais;
        return $this;
    }

    /**
     * Get noteDeFrais.
     *
     * @return NoteDeFrais
     */
    public function getNoteDeFrais()
    {
        return $this->noteDeFrais;
    }

    /**
     * Set compte.
     *
     * @param Compte $compte
     *
     * @return NoteDeFraisDetail
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
     * Get TVA on peages
     * @return float
     */
    public function getTvaPeages()
    {
        return $this->tvaPeages;
    }

    /**
     * Set TVA on peages
     * @param float $tvaPeages
     * @return NoteDeFraisDetail
     */
    public function setTvaPeages(float $tvaPeages)
    {
        $this->tvaPeages = $tvaPeages;
        return $this;
    }

}
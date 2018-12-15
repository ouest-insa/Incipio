<?php

namespace Mgate\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Indicateur
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=15, nullable=false)
     */
    private $categorie;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $titre;

    /**
     * @var string
     * @ORM\Column(type="string", length=127, nullable=false)
     */
    private $methode;

    /**
     * @var bool
     * @ORM\Column(type="boolean",  nullable=true)
     */
    private $options;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * @param string $categorie
     *
     * @return Indicateur
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param string $titre
     *
     * @return Indicateur
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethode()
    {
        return $this->methode;
    }

    /**
     * @param string $methode
     *
     * @return Indicateur
     */
    public function setMethode($methode)
    {
        $this->methode = $methode;

        return $this;
    }

    public function hasOptions()
    {
        return $this->options;
    }

    /**
     * @param string $methode
     *
     * @return Indicateur
     */
    public function setOptions($x)
    {
        $this->options = $x;

        return $this;
    }
}

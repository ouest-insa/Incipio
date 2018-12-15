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
 * Compte.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Compte
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
     * @var int
     *
     * @ORM\Column(name="numero", type="string", length=6, unique=true)
     */
    private $numero;

    /**
     * @var bool
     *
     * @ORM\Column(name="categorie", type="boolean")
     */
    private $categorie;

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
     * @return Compte
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
     * Set numero.
     *
     * @param int $numero
     *
     * @return Compte
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
     * Set categorie.
     *
     * @param bool $categorie
     *
     * @return Compte
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie.
     *
     * @return bool
     */
    public function isCategorie()
    {
        return $this->categorie;
    }

    /**
     * Get categorie.
     *
     * @return bool
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
}

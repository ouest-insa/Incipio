<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suivi.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Suivi
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
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="suivis", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $etude;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="text")
     */
    private $etat;

    /**
     * @var string
     *
     * @ORM\Column(name="todo", type="text")
     */
    private $todo;

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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Suivi
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

    /**
     * Set etat.
     *
     * @param string $etat
     *
     * @return Suivi
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat.
     *
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set todo.
     *
     * @param string $todo
     *
     * @return Suivi
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

        return $this;
    }

    /**
     * Get todo.
     *
     * @return string
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return Suivi
     */
    public function setEtude($etude)
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

    public function __toString()
    {
        return $this->getEtude()->getNom() . ' ' . $this->getTodo();
    }
}

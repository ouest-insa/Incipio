<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Project;

use App\Entity\Comment\Thread;
use App\Entity\Personne\Personne;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\Project\ClientContactRepository")
 */
class ClientContact
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
     * @var Etude
     *
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="clientContacts", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $etude;

    /**
     * @var Personne
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Personne\Personne")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faitPar;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var Thread
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Comment\Thread",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="objet", type="text",nullable=true)
     */
    private $objet;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="text",nullable=true)
     */
    private $contenu;

    /**
     * @var string
     *
     * @ORM\Column(name="moyenContact", type="text",nullable=true)
     */
    private $moyenContact;

    public function __construct()
    {
        $this->date = new \DateTime('now');
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
     * Set etude.
     *
     * @param string $etude
     *
     * @return ClientContact
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

    /**
     * Set faitPar.
     *
     * @param Personne $faitPar
     *
     * @return ClientContact
     */
    public function setFaitPar(Personne $faitPar)
    {
        $this->faitPar = $faitPar;

        return $this;
    }

    /**
     * Get faitPar.
     *
     * @return Personne
     */
    public function getFaitPar()
    {
        return $this->faitPar;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return ClientContact
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
     * Set thread.
     *
     * @param Thread $thread
     *
     * @return ClientContact
     */
    public function setThread(Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread.
     *
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set contenu.
     *
     * @param string $contenu
     *
     * @return ClientContact
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu.
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set moyenContact.
     *
     * @param string $moyenContact
     *
     * @return ClientContact
     */
    public function setMoyenContact($moyenContact)
    {
        $this->moyenContact = $moyenContact;

        return $this;
    }

    /**
     * Get moyenContact.
     *
     * @return string
     */
    public function getMoyenContact()
    {
        return $this->moyenContact;
    }

    /**
     * Set objet.
     *
     * @param string $objet
     *
     * @return ClientContact
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

    public function __toString()
    {
        return $this->getId() . ' - ' . $this->getFaitPar()->__toString() . ' ' . $this->getObjet();
    }
}

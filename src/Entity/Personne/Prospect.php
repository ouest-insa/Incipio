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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Mgate\CommentBundle\Entity\Thread;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mgate\PersonneBundle\Entity\Prospect.
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Mgate\PersonneBundle\Entity\ProspectRepository")
 */
class Prospect extends Adressable
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
     * @ORM\OneToMany(targetEntity="Employe", mappedBy="prospect")
     */
    private $employes;

    /**
     * @ORM\OneToOne(targetEntity="\Mgate\CommentBundle\Entity\Thread", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $thread;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Groups({"gdpr"})
     *
     * @ORM\Column(name="nom", type="string", length=63)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="entite", type="integer", nullable=true)
     * @Assert\Choice(callback = "getEntiteChoiceAssert")
     */
    private $entite;

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
     * @ORM\PostPersist
     */
    public function createThread(LifecycleEventArgs $args)
    {
        if (null === $this->getThread()) {
            $em = $args->getEntityManager();
            $t = new Thread();
            $t->setId('prospect_' . $this->getId());
            $this->setThread($t);
            $this->getThread()->setPermalink('fake');
            $em->persist($t);
            $em->flush();
        }
    }

    public function __toString()
    {
        return 'Prospect ' . $this->nom;
    }

    /**
     * Set thread.
     *
     * @param Thread $thread
     *
     * @return Prospect
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
     * Add employes.
     *
     * @param Employe $employes
     *
     * @return Prospect
     */
    public function addEmploye(Employe $employes)
    {
        $this->employes[] = $employes;

        return $this;
    }

    /**
     * Remove employes.
     *
     * @param Employe $employes
     */
    public function removeEmploye(Employe $employes)
    {
        $this->employes->removeElement($employes);
    }

    /**
     * Get employes.
     *
     * @return ArrayCollection
     */
    public function getEmployes()
    {
        return $this->employes;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return Prospect
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->employes = new ArrayCollection();
    }

    /**
     * Set entite.
     *
     * @param string $entite
     *
     * @return Prospect
     */
    public function setEntite($entite)
    {
        $this->entite = $entite;

        return $this;
    }

    /**
     * Get entite.
     *
     * @return string
     */
    public function getEntite()
    {
        return $this->entite;
    }

    public static function getEntiteChoice()
    {
        return [
            1 => 'Particulier',
            2 => 'Association',
            3 => 'TPE (moins de 20 salariés)',
            4 => 'PME / ETI (plus de 20 salariés)',
            5 => 'Grand Groupe',
            6 => 'Ecole',
            7 => 'Administration',
            8 => 'Junior-Entreprise',
            ];
    }

    public static function getEntiteChoiceAssert()
    {
        return array_keys(self::getEntiteChoice());
    }

    public function getEntiteToString()
    {
        if (!$this->entite) {
            return '';
        }
        $tab = $this->getEntiteChoice();

        return $tab[$this->entite];
    }
}

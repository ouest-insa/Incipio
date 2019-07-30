<?php



namespace App\Entity\Project;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Devis extends DocType
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
     * @ORM\OneToOne(targetEntity="Etude", mappedBy="cc")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $etude;

    public function getReference()
    {
        return $this->etude->getReference() . '/' . (null !== $this->getDateSignature() ? $this->getDateSignature()
                ->format('Y') : '') . '/DEVIS/' . $this->getVersion();
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
     * Set etude.
     *
     * @param Etude $etude
     *
     * @return Devis
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

    public function __toString()
    {
        return $this->etude->getReference() . '/DEVIS/';
    }
}

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
 * @ORM\Table()
 * @ORM\Entity
 */
class ProcesVerbal extends DocType
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
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="procesVerbaux", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $etude;

    // Justification du choix: choix des phases dans un select multiple destiner qu'à un affichage, aucun traitement sur les phases

    /**
     * @var string
     *
     * @ORM\Column(name="phaseIDs", type="integer", nullable=true)
     */
    protected $phaseID;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    private $type;

    /**
     * Array used during one of the processing. Declared here to avoid dynamically declared field.
     *
     * @var array
     */
    private $phaseIDs;

    /*
    * ADDITIONAL
    */
    public function getReference()
    {
        return $this->etude->getReference() . '/' . (null !== $this->etude->getCc()->getDateSignature() ?
                $this->etude->getCc()->getDateSignature()->format('Y') : '') . '/PVR/' . $this->getVersion();
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
     * @param Etude $etude
     *
     * @return ProcesVerbal
     */
    public function setEtude(Etude $etude)
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
     * Set type.
     *
     * @param string $type
     *
     * @return ProcesVerbal
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set phaseIDs.
     *
     * @param array $phaseIDs
     *
     * @return ProcesVerbal
     */
    public function setPhaseIDs($phaseIDs)
    {
        $this->phaseIDs = $phaseIDs;

        return $this;
    }

    /**
     * Get phaseIDs.
     *
     * @return array
     */
    public function getPhaseIDs()
    {
        return $this->phaseIDs;
    }

    /**
     * Set phaseID.
     *
     * @param int $phaseID
     *
     * @return ProcesVerbal
     */
    public function setPhaseID($phaseID)
    {
        $this->phaseID = $phaseID;

        return $this;
    }

    /**
     * Get phaseID.
     *
     * @return int
     */
    public function getPhaseID()
    {
        return $this->phaseID;
    }

    public function __toString()
    {
        return $this->etude->getReference() . '/PVR/';
    }
}

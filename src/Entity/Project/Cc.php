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
class Cc extends DocType
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
                ->format('Y') : '') . '/CC/' . $this->getVersion();
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
     * @return Cc
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
        return $this->etude->getReference() . '/CC/';
    }
}

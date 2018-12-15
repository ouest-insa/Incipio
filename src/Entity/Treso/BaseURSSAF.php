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
 * BaseURSSAF.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mgate\TresoBundle\Entity\BaseURSSAFRepository")
 */
class BaseURSSAF
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
     * @var float
     *
     * @ORM\Column(name="baseURSSAF", type="decimal", precision=4, scale=2)
     */
    private $baseURSSAF;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDebut", type="date")
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFin", type="date")
     */
    private $dateFin;

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
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     *
     * @return BaseURSSAF
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     *
     * @return BaseURSSAF
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set baseURSSAF.
     *
     * @param string $baseURSSAF
     *
     * @return BaseURSSAF
     */
    public function setBaseURSSAF($baseURSSAF)
    {
        $this->baseURSSAF = $baseURSSAF;

        return $this;
    }

    /**
     * Get baseURSSAF.
     *
     * @return string
     */
    public function getBaseURSSAF()
    {
        return $this->baseURSSAF;
    }
}

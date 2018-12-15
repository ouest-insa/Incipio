<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 29/01/2017
 * Time: 10:35.
 */

namespace Mgate\DashboardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AdminParam.
 *
 * @ORM\Entity()
 */
class AdminParam
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=31)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $paramType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $defaultValue;

    /**
     * @ORM\Column(type="boolean")
     */
    private $required;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $paramLabel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paramDescription;

    /**
     * The priority in display order. the higher priority is, the higher in the list it will be.
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $priority;

    /** Auto generated methods */

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getParamType()
    {
        return $this->paramType;
    }

    /**
     * @param mixed $paramType
     */
    public function setParamType($paramType)
    {
        $this->paramType = $paramType;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return mixed
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return mixed
     */
    public function getParamLabel()
    {
        return $this->paramLabel;
    }

    /**
     * @param mixed $paramLabel
     */
    public function setParamLabel($paramLabel)
    {
        $this->paramLabel = $paramLabel;
    }

    /**
     * @return mixed
     */
    public function getParamDescription()
    {
        return $this->paramDescription;
    }

    /**
     * @param mixed $paramDescription
     */
    public function setParamDescription($paramDescription)
    {
        $this->paramDescription = $paramDescription;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }
}

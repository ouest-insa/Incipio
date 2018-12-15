<?php

namespace Mgate\SuiviBundle\Entity;

class PhaseChange
{
    private $position = false;

    private $nbrJEH = false;

    private $prixJEH = false;

    private $titre = false;

    private $objectif = false;

    private $methodo = false;

    private $dateDebut = false;

    private $validation = false;

    private $delai = false;

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($x)
    {
        $this->position = $x;

        return $this;
    }

    public function getNbrJEH()
    {
        return $this->nbrJEH;
    }

    public function getPrixJEH()
    {
        return $this->prixJEH;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getObjectif()
    {
        return $this->objectif;
    }

    public function getMethodo()
    {
        return $this->methodo;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function getValidation()
    {
        return $this->validation;
    }

    public function getDelai()
    {
        return $this->delai;
    }

    public function setNbrJEH($x)
    {
        $this->nbrJEH = $x;

        return $this;
    }

    public function setPrixJEH($x)
    {
        $this->prixJEH = $x;

        return $this;
    }

    public function setTitre($x)
    {
        $this->titre = $x;

        return $this;
    }

    public function setObjectif($x)
    {
        $this->objectif = $x;

        return $this;
    }

    public function setMethodo($x)
    {
        $this->methodo = $x;

        return $this;
    }

    public function setDateDebut($x)
    {
        $this->dateDebut = $x;

        return $this;
    }

    public function setValidation($x)
    {
        $this->validation = $x;

        return $this;
    }

    public function setDelai($x)
    {
        $this->delai = $x;

        return $this;
    }
}

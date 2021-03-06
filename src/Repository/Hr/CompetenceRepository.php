<?php

namespace App\Repository\Hr;

use App\Entity\Hr\Competence;
use Doctrine\ORM\EntityRepository;

class CompetenceRepository extends EntityRepository
{
    /**
     * Méthode retournant toutes les compétences et leurs membres associés.
     *
     * @return array
     */
    public function getCompetencesTree()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('c')
            ->from(Competence::class, 'c')
            ->leftJoin('c.membres', 'membres')
            ->addSelect('membres')
            ->orderBy('c.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Returns an array of etudes and phases with their associated competences.
     *
     * @return array
     */
    public function getAllEtudesByCompetences()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('c')
            ->from(Competence::class, 'c')
            ->leftJoin('c.etudes', 'etudes')
            ->addSelect('etudes')
            ->leftJoin('etudes.phases', 'phases')
            ->addSelect('phases')
            ->orderBy('c.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }
}

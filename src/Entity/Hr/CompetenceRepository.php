<?php

namespace N7consulting\RhBundle\Entity;

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
            ->from('N7consultingRhBundle:Competence', 'c')
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
            ->from('N7consultingRhBundle:Competence', 'c')
            ->leftJoin('c.etudes', 'etudes')
            ->addSelect('etudes')
            ->leftJoin('etudes.phases', 'phases')
            ->addSelect('phases')
            ->orderBy('c.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }
}

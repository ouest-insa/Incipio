<?php

namespace Mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ClientContactRepository.
 */
class ClientContactRepository extends EntityRepository
{
    /** Returns all contacts for an Etude */
    public function getByEtude(Etude $etude, array $order = ['id' => 'asc'])
    {
        $qb = $this->_em->createQueryBuilder();

        $key = key($order);
        $query = $qb->select('cc')
            ->from('MgateSuiviBundle:ClientContact', 'cc')
            ->leftJoin('cc.faitPar', 'faitPar')
            ->addSelect('faitPar')
            ->where('cc.etude = :etude')
            ->setParameter('etude', $etude)
            ->orderBy('cc.' . $key, $order[$key])
            ->getQuery();

        return $query->getResult();
    }

    /** Returns the last contact for an Etude */
    public function getLastByEtude(Etude $etude)
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('cc')
            ->from('MgateSuiviBundle:ClientContact', 'cc')
            ->leftJoin('cc.faitPar', 'faitPar')
            ->addSelect('faitPar')
            ->where('cc.etude = :etude')
            ->setParameter('etude', $etude)
            ->orderBy('cc.date DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getResult();
    }
}

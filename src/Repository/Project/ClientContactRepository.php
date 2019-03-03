<?php

namespace App\Repository\Project;

use App\Entity\Project\ClientContact;
use App\Entity\Project\Etude;
use Doctrine\ORM\EntityRepository;

/**
 * ClientContactRepository.
 */
class ClientContactRepository extends EntityRepository
{
    /** Returns all contacts for an Etude
     * @param Etude $etude
     * @param array $order
     *
     * @return mixed
     */
    public function getByEtude(Etude $etude, array $order = ['id' => 'asc'])
    {
        $qb = $this->_em->createQueryBuilder();

        $key = key($order);
        $query = $qb->select('cc')
            ->from(ClientContact::class, 'cc')
            ->leftJoin('cc.faitPar', 'faitPar')
            ->addSelect('faitPar')
            ->where('cc.etude = :etude')
            ->setParameter('etude', $etude)
            ->orderBy('cc.' . $key, $order[$key])
            ->getQuery();

        return $query->getResult();
    }

    /** Returns the last contact for an Etude
     * @param Etude $etude
     *
     * @return mixed
     */
    public function getLastByEtude(Etude $etude)
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb->select('cc')
            ->from(ClientContact::class, 'cc')
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

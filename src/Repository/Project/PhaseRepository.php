<?php

namespace App\Repository\Project;

use App\Entity\Project\Etude;
use App\Entity\Project\Phase;
use Doctrine\ORM\EntityRepository;

/**
 * PhaseRepository.
 */
class PhaseRepository extends EntityRepository
{
    /**
     * @param Etude $etude
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByEtudeQuery(Etude $etude)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from(Phase::class, 'p')
            ->where('p.etude = :etude')
            ->setParameter('etude', $etude);

        return $qb;
    }
}

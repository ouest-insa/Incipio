<?php

namespace Mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PhaseRepository.
 */
class PhaseRepository extends EntityRepository
{
    /**
     * @param \Mgate\SuiviBundle\Entity\Etude $etude
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByEtudeQuery(Etude $etude)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from('MgateSuiviBundle:Phase', 'p')
            ->where('p.etude = :etude')
            ->setParameter('etude', $etude);

        return $qb;
    }
}

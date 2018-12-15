<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 29/08/2016
 * Time: 14:18.
 */

namespace Mgate\SuiviBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MissionRepository extends EntityRepository
{
    public function getMissionsBeginBeforeDate(\DateTime $date)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('m')
            ->from('MgateSuiviBundle:Mission', 'm')
            ->leftJoin('m.intervenant', 'intervenant')
            ->addSelect('intervenant')
            ->leftJoin('intervenant.personne', 'personne')
            ->addSelect('personne')
            ->leftJoin('m.repartitionsJEH', 'repartitionsJEH')
            ->addSelect('repartitionsJEH')
            ->leftJoin('m.etude', 'etude')
            ->addSelect('etude')
            ->leftJoin('etude.ap', 'ap')
            ->addSelect('ap')
            ->leftJoin('etude.cc', 'cc')
            ->addSelect('cc')
            ->leftJoin('etude.prospect', 'prospect')
            ->addSelect('prospect')
            ->leftJoin('etude.suiveur', 'suiveur')
            ->addSelect('suiveur')
            ->leftJoin('etude.phases', 'phases')
            ->addSelect('phases')
            ->where('m.debutOm <= :date')
            ->orderBy('m.finOm', 'DESC')
            ->setParameters(['date' => $date]);

        return $qb->getQuery()->getResult();
    }

    public function getMissionAndEtudeQueryBuilder()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('m')
            ->from('MgateSuiviBundle:Mission', 'm')
            ->leftJoin('m.etude', 'etude')
            ->addSelect('etude');

        return $qb;
    }
}

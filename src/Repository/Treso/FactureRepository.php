<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\Treso;

use Doctrine\ORM\EntityRepository;

class FactureRepository extends EntityRepository
{
    /**
     * Renvoie les facture d'achat ou de vente sur un mois selon la date d'emission pour les factures d'achat et
     * d'encaissement pour les factures de vente. YEAR MONTH DAY sont défini dans DashBoardBundle/DQL.
     *
     * @param int  $type
     * @param int  $month
     * @param int  $year
     * @param bool $trimestriel
     *
     * @return array
     */
    public function findAllTVAByMonth($type, $month, $year, $trimestriel = false)
    {
        $date = (1 == $type ? 'dateEmission' : 'dateVersement');
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('f')
            ->from('MgateTresoBundle:Facture', 'f')
            ->where('f.type ' . (Facture::TYPE_ACHAT == $type ? '=' : '>') . ' ' . Facture::TYPE_ACHAT);
        if ($trimestriel) {
            $query->andWhere('MONTH(f.' . $date . ') >= :month')
                ->setParameter('month', $month)
                ->andWhere('MONTH(f.' . $date . ') < (:month + 2)');
        } else {
            $query->where("MONTH(f.$date) = $month");
        }

        $query->andWhere('YEAR(f.' . $date . ') = :year')
            ->setParameter('year', $year)
            ->orderBy('f.' . $date);

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la somme des montant HT des factures pour les études d'un mandat.
     * sum(montantADeduire) is in fact always a sum of only one item. however the query won't work without (fullgroup
     * mode). Coalesce returns the first not null argument. Allow query to return a result, even though
     * sum(montantADeduire) returns null.
     *
     * @param int       $mandat mandat des etudes dont les factures seront prises en compte
     * @param bool|null $paid   est-ce que seul les factures payées doivent être prises en compte
     *
     * @return float|null
     */
    public function getCAFacture(int $mandat, ?bool $paid = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('sum(fd.montantHT) as montant')
            ->from('MgateTresoBundle:Facture', 'facture')
            ->leftJoin('facture.details', 'fd')
            ->where('facture.type  in (:type)')
            ->setParameter('type', [Facture::TYPE_VENTE_ACCOMPTE, Facture::TYPE_VENTE_INTERMEDIAIRE, Facture::TYPE_VENTE_SOLDE])
            ->leftJoin('facture.etude', 'etude')
            ->andWhere('etude.mandat = :mandat')
            ->setParameter('mandat', $mandat);

        if (null !== $paid && $paid) {
            $qb->andWhere('facture.dateVersement IS NOT NULL');
        }
        $detailsSum = $qb->getQuery()->getSingleScalarResult();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('sum(montantADeduire.montantHT) as montant')
            ->from('MgateTresoBundle:Facture', 'facture')
            ->leftJoin('facture.montantADeduire', 'montantADeduire')
            ->where('facture.type  in (:type)')
            ->setParameter('type', [Facture::TYPE_VENTE_ACCOMPTE, Facture::TYPE_VENTE_INTERMEDIAIRE, Facture::TYPE_VENTE_SOLDE])
            ->leftJoin('facture.etude', 'etude')
            ->andWhere('etude.mandat = :mandat')
            ->setParameter('mandat', $mandat);

        if (null !== $paid && $paid) {
            $qb->andWhere('facture.dateVersement IS NOT NULL');
        }
        $deduireSum = $qb->getQuery()->getSingleScalarResult();

        return $detailsSum - $deduireSum;
    }

    /**
     * Returns the same result as findAll() but with the join on FactureDetails.
     *
     * @return array
     */
    public function getFactures()
    {
        $qb = $this->_em->createQueryBuilder();

        $query = $qb
            ->select('f')
            ->from('MgateTresoBundle:Facture', 'f')
            ->leftJoin('f.details', 'details')
            ->addSelect('details')
            ->leftJoin('f.montantADeduire', 'montantADeduire')
            ->addSelect('montantADeduire')
            ->getQuery();

        return $query->getResult();
    }
}

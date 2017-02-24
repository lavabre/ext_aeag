<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgSandreCodesAlternAppelTaxonRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreCodesAlternAppelTaxon() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreCodesAlternAppelTaxon p";
        $query = $query . " order by p.codeAppelTaxon";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreCodesAlternAppelTaxonByCodeAppeltaxon($codeAppelTaxon) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreCodesAlternAppelTaxon p";
        $query = $query . " where p.codeAppelTaxon = :codeAppelTaxon";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeAppelTaxon', $codeAppelTaxon);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreCodesAlternAppelTaxonBycodeAlternOrigineCodeAltern($codeAltern, $origineCodeAltern) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreCodesAlternAppelTaxon p";
        $query = $query . " where p.codeAltern = :codeAltern";
        $query = $query . " and p.origineCodeAltern = :origineCodeAltern";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeAltern', $codeAltern);
        $qb->setParameter('origineCodeAltern', $origineCodeAltern);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

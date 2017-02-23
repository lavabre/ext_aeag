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
class PgSandreAppellationTaxonRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreAppellationTaxon() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreAppellationTaxon p";
        $query = $query . " order by p.codeAppelTaxon";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreAppellationTaxonByCodeAppeltaxon($codeAppelTaxon) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreAppellationTaxon p";
        $query = $query . " where p.codeAppelTaxon = :codeAppelTaxon";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeAppelTaxon', $codeAppelTaxon);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($codeAppelTaxon, $codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreAppellationTaxon p";
        $query = $query . " where p.codeAppelTaxon = :codeAppelTaxon";
        $query = $query . " and p.codeSupport = :codeSupport";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeAppelTaxon', $codeAppelTaxon);
        $qb->setParameter('codeSupport', $codeSupport);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

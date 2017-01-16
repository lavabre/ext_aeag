<?php

/**
 * Description of PgProgGrparObligSupportRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgGrparObligSupportRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgGrparObligSupportRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgGrparObligSupports() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparObligSupport p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgGrparObligSupportByGrparRefId($grparRefId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparObligSupport p";
        $query = $query . " where p.grparRefId = :grparRefId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('grparRefId', $grparRefId);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgGrparObligSupportByCodeSupport($codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparObligSupport p";
        $query = $query . " where p.codeSupport = :codeSuppor";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeSupport', $codeSupport);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgGrparObligSupportByGrparRefIdCodeSupport($grparRefId, $codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparObligSupport p";
        $query = $query . " where p.grparRefId = :grparRefId";
        $query = $query . " and p.codeSupport = ':codeSupport";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('grparRefId', $grparRefId);
        $qb->setParameter('codeSupport', $codeSupport);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

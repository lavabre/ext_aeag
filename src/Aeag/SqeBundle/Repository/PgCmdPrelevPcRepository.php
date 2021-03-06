<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdPrelevPcRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdPrelevPcRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdPrelevPcs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevPcByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $query = $query . " order by c.zoneVerticale ";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelev->getId());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevPcByPrelevOrderByProfondeur($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $query = $query . " order by c.profondeur ";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelev->getId());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $numOrdre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $query = $query . " and c.numOrdre = :numOrdre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelev->getId());
        $qb->setParameter('numOrdre', $numOrdre);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getMaxNumOrdreByPrelev($pgCmdPrelev) {
        $query = "select max(c.numOrdre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelev->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

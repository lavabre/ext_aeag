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
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $numOrdre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query . " and c.numOrdre = " . $numOrdre ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getMaxNumOrdreByPrelev($pgCmdPrelev) {
        $query = "select max(c.numOrdre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelevPc c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    
}

<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdSuiviPrelRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdSuiviPrelRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdSuiviPrels() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdSuiviPrel c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

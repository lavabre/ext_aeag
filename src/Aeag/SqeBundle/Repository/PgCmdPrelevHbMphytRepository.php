<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdPrelevHbMphytRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdPrelevHbMphytRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdPrelevHbMphyts() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbMphyt c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevHbMphytByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbMphyt c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

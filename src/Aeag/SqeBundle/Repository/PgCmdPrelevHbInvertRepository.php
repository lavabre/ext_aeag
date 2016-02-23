<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdPrelevHbInvertRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdPrelevHbInvertRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdPrelevHbInverts() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbInvert c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevHbInvertByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbInvert c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

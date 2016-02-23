<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertPrelemRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertPrelemRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPPgCmdInvertPrelems() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertPrelem c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPPgCmdInvertPrelemByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertPrelem c";
        $query = $query . " where c.prelev = " . $pgCmdPrelevHbInvert->getPrelev() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

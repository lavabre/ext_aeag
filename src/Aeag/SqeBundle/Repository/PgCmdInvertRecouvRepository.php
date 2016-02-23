<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertRecouvRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertRecouvRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPPgCmdInvertRecouvs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertRecouv c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPPgCmdInvertRecouvByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertRecouv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelevHbInvert->getPrelev() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

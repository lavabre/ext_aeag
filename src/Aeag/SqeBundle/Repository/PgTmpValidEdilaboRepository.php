<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgTmpValidEdilaboRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgTmpValidEdilaboRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgTmpValidEdilabos() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgTmpValidEdilabo c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgTmpValidEdilaboByDemandeId($demandeid) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgTmpValidEdilabo c";
        $query = $query . " where c.demandeId = " . $demandeId ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

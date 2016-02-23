<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdAnalyseRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdAnalyseRepository extends EntityRepository {
    
     /**
     * @return array
     */
    public function getPgCmdAnalyses() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotAnByPrelevId($prelevId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $prelevId;
        $query = $query . " order by p.numOrdre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
    
     public function getPgProgLotAnByPrelevIdNumOrdre($prelevId, $numOrdre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $prelevId;
        $query = $query  . " and p.numOrdre = " . $numOrdre;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
          return $qb->getOneOrNullResult();
    }
    
}

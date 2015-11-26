<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotGrparRefRepository extends EntityRepository {

    public function getPgProgLotGrparRefByProgLotGrparRef($ProgLotGrparOprel) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
         $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgProgLotGrparRefByLot($pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.lot = " . $pgProgLot->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotGrparRefByGrpparRef($PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.grpparref = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparRefByPgProgLotPgProgGrpParamRef($PgProgLot, $PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.lot_id = " . $PgProgLot->getId();
        $query = $query . " where p.grpar_ref_id = " . $PgProgGrpParamRef->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
  

}

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

//    public function getPgProgLotGrparRefByProgLotGrparRef($progLotGrparOprel) {
//        $query = "select p";
//        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
//         $qb = $this->_em->createQuery($query);
//         //print_r($query);
//        return $qb->getResult();
//    }

    public function getPgProgLotGrparRefByLot($pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.lot = :pgProgLot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparRefByGrpparRef($pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.grpparref = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparRefByPgProgLotPgProgGrpParamRef($pgProgLot, $pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparRef p";
        $query = $query . " where p.lot_id = :pgProgLot";
        $query = $query . " where p.grpar_ref_id = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getId());
        //print_r($query);
        return $qb->getResult();
    }

}

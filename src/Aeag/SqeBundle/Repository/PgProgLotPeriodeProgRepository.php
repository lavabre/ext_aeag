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
class PgProgLotPeriodeProgRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProg() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProgById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeProgByStationAn($PgProgLotStationAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgByGrparAn($PgProglotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
    public function getPgProgLotPeriodeProgByPeriodeAn($PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

   public function getPgProgLotPeriodeProgByStationAnGrparAn($PgProgLotStationAn,$PgProglotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.grparAn = " . $PgProglotGrparAn->getId();
         $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgByStationAnPeriodeAn($PgProgLotStationAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
       // print_r($query);
       return $qb->getResult();
    }
    
    
    public function getPgProgLotPeriodeProgByStationAnGrparAnPeriodeAn($PgProgLotStationAn,$PgProglotGrparAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.grparAn = " . $PgProglotGrparAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     public function getPgProgLotPeriodeProgByGrparAnPeriodeAn($PgProglotGrparAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function countPgProgLotPeriodeProgByGrparAn($PgProglotGrparAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function countPgProgLotPeriodeProgByStationAn($PgProgLotStationAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function countPgProgLotPeriodeProgByPeriodeAn($PgProglotPeriodeAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function countStationAnByGrparAn($PgProglotGrparAn) {
        $query = "select count( distinct p.stationAn)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function getPgProgLotPeriodeProgByPprogCompl($PgProgLotPeriodeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.pprogCompl = " . $PgProgLotPeriodeProg->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
  
}

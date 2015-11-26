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
class PgProgLotPeriodeProgMajRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProgMaj() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProgMajById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeProgMajByStationAn($stationAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.stationAn = " . $stationAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgMajByGrparAn($grparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.grparAn = " . $grparAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgMajByPeriodeAn($periodan) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.periodan = " . $periodan;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgMajByStationAnGrparAn($stationAn, $grparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.stationAn = " . $stationAn;
        $query = $query . " and p.grparAn = " . $grparAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgMajByStationAnPeriodeAn($stationAn, $periodan) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.stationAn = " . $stationAn;
        $query = $query . " and p.periodan = " . $periodan;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgMajByStationAnGrparAnPeriodeAn($stationAn, $grparAn, $periodan) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.stationAn = " . $stationAn;
        $query = $query . " and p.grparAn = " . $grparAn;
        $query = $query . " and p.periodan = " . $periodan;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeProgMajByGrparAnPeriodeAn($grparAn, $periodan) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.grparAn = " . $grparAn;
        $query = $query . " and p.periodan = " . $periodan;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function countPgProgLotPeriodeProgMajByGrparAn($grparAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.grparAn = " . $grparAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function countPgProgLotPeriodeProgMajByStationAn($stationAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.stationAn = " . $stationAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function countPgProgLotPeriodeProgMajByPeriodeAn($periodan) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.periodan = " . $periodan;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function countStationAnByGrparAn($grparAn) {
        $query = "select count( distinct p.stationAn)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.grparAn = " . $grparAn;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgProgLotPeriodeProgMajByPprogCompl($pprogCompl) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProgMaj p";
        $query = $query . " where p.pprogCompl = " . $pprogCompl;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

<?php

/**
 * Description of pgProgZgeorefStationRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class pgProgZgeorefStationRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgZgeorefStationRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getpgProgZgeorefStation() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefStation p";
        $query = $query . " order by p.zgeoref, p.stationMesure";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getpgProgZgeorefStationByZgeoref($pgProgZoneGeoref) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefStation p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgProgZgeorefStationByZgeoref($pgProgZoneGeoref) {
        $query = "select count(p.stationMesure)";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefStation p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getpgProgZgeorefStationByStationMesure($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefStation p";
        $query = $query . " where p.stationMesure = :pgRefStationMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getpgProgZgeorefStationByZgeorefStaionMesure($pgProgZoneGeoref, $pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefStation p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $query = $query . " and p.stationMesure = :pgRefStationMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of PgRefStationRsxRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgRefStationRsxRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgRefStationRsxRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefStationRsx() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationRsx p";
        $query = $query . " order by p.reseauMesure, p.stationMesure";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefStationRsxByResauMesure($pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationRsx p";
        $query = $query . " where p.reseauMesure = " . $pgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getNbPgRefStationRsxByReseauMesure($pgRefReseauMesure) {
        $query = "select count(p.stationMesure)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationRsx p";
        $query = $query . " where p.reseauMesure = " . $pgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }


    /**
     * @return array
     */
    public function getPgRefStationRsxByStationMesure($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationRsx p";
        $query = $query . " where p.stationMesure = " . $pgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgRefStationRsxByReseauMesureStaionMesure($pgRefReseauMesure, $pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationRsx p";
        $query = $query . " where p.reseauMesure = " . $pgRefReseauMesure->getgroupementId();
        $query = $query . " and p.stationMesure = " . $pgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

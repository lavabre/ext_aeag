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
class PgProgZgeorefTypmilRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getpgProgZgeorefTypmil() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\pgProgZgeorefTypmil p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getpgProgZgeorefTypmilByZgeoref($pgProgZoneGeoref) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZgeorefTypmil p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgProgZgeorefTypmilByZgeoref($pgProgZoneGeoref) {
        $query = "select count(p.codeMilieu)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZgeorefTypmil p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getpgProgZgeorefTypmilByTypmil($pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZgeorefTypmil p";
        $query = $query . " where p.codeMilieu = :pgProgTypeMilieu";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        //print_r($query);
        return $qb->getResult();
    }

    public function getpgProgZgeorefTypmilByZgeorefTypmil($pgProgZoneGeoref, $pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZgeorefTypmil p";
        $query = $query . " where p.zgeoref = :pgProgZoneGeoref";
        $query = $query . " and p.codeMilieu = :pgProgTypeMilieu";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgZoneGeoref', $pgProgZoneGeoref->getId());
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

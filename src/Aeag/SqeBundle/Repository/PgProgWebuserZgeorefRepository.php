<?php

/**
 * Description of PgProgWebuserZgeorefRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgWebuserZgeorefRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgWebuserZgeorefRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgWebuserZgeoref() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserZgeoref p";
        $query = $query . " order by p.webuser";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserZgeorefByZgeoref($pgProgZoneGeoRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserZgeoref p";
        $query = $query . " where p.zgeoref = " . $pgProgZoneGeoRef->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserZgeorefByWebuser($pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserZgeoref p";
        $query = $query . " where p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgWebuserZgeorefByWebuserZgeoref($pgProgWebusers, $pgProgZoneGeoRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserZgeoref p";
        $query = $query . " where p.zgeoref = " . $pgProgZoneGeoRef->getId();
        $query = $query . " and p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

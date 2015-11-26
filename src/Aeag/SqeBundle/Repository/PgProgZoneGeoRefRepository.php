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
class PgProgZoneGeoRefRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgZoneGeoRefs() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZoneGeoRef p";
        $query = $query . " order by p.nomZoneGeo";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgZoneGeoRefById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZoneGeoRef p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgZoneGeoRefByNomZoneGeo($nomZoneGeo) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZoneGeoRef p";
        $query = $query . " where p.nomZoneGeo = '" . $nomZoneGeo . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgProgZoneGeoRefByTypeZoneGeo($typeZoneGeo) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZoneGeoRef p";
        $query = $query . " where p.typeZoneGeo = '" . $typeZoneGeo . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }


    public function getPgProgZoneGeoRefByTypeMilieu($PgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgZoneGeoRef p";
        $query = $query . " where p.typeMilieu = " . $PgProgTypeMilieu->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

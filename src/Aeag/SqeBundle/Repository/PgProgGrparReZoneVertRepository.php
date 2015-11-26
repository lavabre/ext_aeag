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
class PgProgGrparRefZoneVertRepository extends EntityRepository {

    

    public function getPgProgGrparRefZoneVertByGrparRef($PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getNbPgProgGrparRefZoneVertByGrparRef($PgProgGrpParamRef) {
        $query = "select count(p)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

  

}

<?php

/**
 * Description of RegionRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RegionRepository
 * @package Aeag\AeagBundle\Repository
 */
class RegionRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getRegions() {

        $query = "select r";
        $query = $query . " from Aeag\AeagBundle\Entity\Region r";
        $query = $query . " order by r.reg";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getRegionsBydec() {

        $query = "select r";
        $query = $query . " from Aeag\AeagBundle\Entity\Region r";
        $query = $query . " where r.dec = 'O'";
        $query = $query . " order by r.reg";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getRegionByReg($reg) {

        $query = "select r";
        $query = $query . " from Aeag\AeagBundle\Entity\Region r";
        $query = $query . " where r.reg = '" . $reg . "'";
        $qb = $this->_em->createQuery($query);

       // print_r($query);
        return $qb->getOneOrNullResult();
    }

}

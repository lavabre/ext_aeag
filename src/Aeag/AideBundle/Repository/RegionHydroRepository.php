<?php

/**
 * Description of RegionHydroRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RegionHydroRepository
 * @package Aeag\AideBundle\Repository
 */
class RegionHydroRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getRegionHydros() {

        $query = "select r";
        $query = $query . " from Aeag\AideBundle\Entity\RegionHydro r";
        $query = $query . " order by r.reg";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getRegionHydro($reg) {

        $query = "select r";
        $query = $query . " from Aeag\AideBundle\Entity\RegionHydro r";
        $query = $query . " where r.reg = :reg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('reg', $reg);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

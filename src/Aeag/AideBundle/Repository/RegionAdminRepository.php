<?php

/**
 * Description of RegionAdminRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RegionAdminRepository
 * @package Aeag\AideBundle\Repository
 */
class RegionAdminRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getRegionAdmins() {

        $query = "select r";
        $query = $query . " from Aeag\AideBundle\Entity\RegionAdmin r";
        $query = $query . " order by r.reg";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getRegionAdmin($reg) {

        $query = "select r";
        $query = $query . " from Aeag\AideBundle\Entity\RegionAdmin r";
        $query = $query . " where r.reg = :reg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('reg', $reg);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

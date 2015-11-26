<?php

/**
 * Description of DepartementRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DepartementRepository
 * @package Aeag\AideBundle\Repository
 */
class DepartementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDepartements() {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Departement d";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getDepartement($dept) {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Departement d";
        $query = $query . " where d.dept = '" . $dept . "'";
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

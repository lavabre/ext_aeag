<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DepartementRepository
 * @package Aeag\AeagBundle\Repository
 */
class AdminDepartementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDepartements() {

        $query = "select d";
        $query = $query . " from Aeag\EdlBundle\Entity\AdminDepartement d";
        $query = $query . " order by d.inseeDepartement";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getDepartementByDept($dept) {

        $query = "select d";
        $query = $query . " from Aeag\EdlBundle\Entity\AdminDepartement d";
        $query = $query . " where d.inseeDepartement = ':dept";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dept', $dept);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

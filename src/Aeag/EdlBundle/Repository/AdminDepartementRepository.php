<?php

/**
 * Description of AdminDepartementRepository
 *
 * @author lavabre
 */

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RefAdminDepartementRepository
 * @package Aeag\DecBundle\Repository
 */
class AdminDepartementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getAdminDepartements() {
        $query = "select c";
        $query = $query . " from Aeag\EdlBundle\Entity\AdminDepartement c";
        $query = $query . " order by c.inseeDepartement";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
   

    /**
     * @return array
     */
    public function getAdminDepartementByInsee($insee) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\AdminDepartement c";
        $query = $query . " where c.inseeDepartement = '" . $insee . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of DepUtilisateurRepository
 *
 * @author lavabre
 */

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DepartementRepository
 * @package Aeag\AeagBundle\Repository
 */
class DepUtilisateurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDepUtilisateurs() {

        $query = "select d";
        $query = $query . " from Aeag\EdlBundle\Entity\DepUtilisateur d";
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
        $query = $query . " from Aeag\EdlBundle\Entity\DepUtilisateur d";
        $query = $query . " where d.inseeDepartement = '" . $dept . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getDepartementByUtilisateur($utilisateur) {

        $query = "select d";
        $query = $query . " from Aeag\EdlBundle\Entity\DepUtilisateur d";
        $query = $query . " where d.utilisateur = " . $utilisateur->getId();

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    public function getDepartementByDeptUtilisateur($dept, $utilisateur) {

        $query = "select d";
        $query = $query . " from Aeag\EdlBundle\Entity\DepUtilisateur d";
        $query = $query . " where d.inseeDepartement = '" . $dept->getInseeDepartement() . "'";
        $query = $query . " and d.utilisateur = " . $utilisateur->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

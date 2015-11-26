<?php

/**
 * Description of CommuneRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CommuneRepository
 * @package Aeag\AeagBundle\Repository
 */
class CommuneRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCommunes() {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " order by d.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getCommunesByDec() {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " where d.dec = 'O'";
        $query = $query . " order by d.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getCommuneByDept($dept) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " where d.Departement = '" . $dept . "'";
        $query = $query . " order by d.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @param $dept
     * @return mixed
     */
    public function getCommuneByDeptDec($dept) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " where d.dec = 'O'";
        $query = $query . " and d.Departement = '" . $dept . "'";
        $query = $query . " order by d.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCommuneByCommune($commune) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " where d.commune = '" . $commune . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @param $commune
     * @return mixed
     */
    public function getCommuneById($id) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Commune d";
        $query = $query . " where d.id = " . $id;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}

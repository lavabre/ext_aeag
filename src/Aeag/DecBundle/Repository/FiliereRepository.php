<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class FiliereRepository
 * @package Aeag\DecBundle\Repository
 */
class FiliereRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getFilieres() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Filiere c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getFilieresAidables($aidable) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Filiere c";
        $query = $query . " where c.aidable = '" . $aidable . "'";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getFiliereByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Filiere c";
        $query = $query . " where c.code = '" . $code . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

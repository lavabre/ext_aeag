<?php

/**
 * Description of PgProgMarcheUserRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgMarcheUserRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgMarcheUser() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarcheUser p";
        $query = $query . " order by p.marche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgMarcheUserByMarche($pgProgMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarcheUser p";
        $query = $query . " where p.marche = " . $pgProgMarche->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getPgProgMarcheUserByUser($pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarcheUser p";
        $query = $query . " where p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgMarcheByMarcheUser($pgProgMarche,$pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarches p";
        $query = $query . " where p.marche = " . $pgProgMarche->getId();
        $query = $query . " and p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

   
}

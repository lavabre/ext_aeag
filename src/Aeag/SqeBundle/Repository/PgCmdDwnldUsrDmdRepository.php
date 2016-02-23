<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdDwnldUsrDmdRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdDwnldUsrDmdRepository extends EntityRepository {
     
    /**
     * @return array
     */
    public function getPgCmdDwnldUsrDmds() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd c";
        $query = $query . " order by c.date";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdDwnldUsrDmdByUser($pgProgWebusers) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd c";
        $query = $query . " where c.user = " . $pgProgWebusers->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdDwnldUsrDmdByDemande($pgCmdDemande) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd c";
        $query = $query . " where c.demande = " . $pgCmdDemande->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
}

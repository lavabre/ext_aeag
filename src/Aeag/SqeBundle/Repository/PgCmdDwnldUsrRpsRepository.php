<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdDwnldUsrRpsRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdDwnldUsrRpsRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPPgCmdDwnldUsrRpss() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps c";
        $query = $query . " order by c.date";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPPgCmdDwnldUsrRpsByUser($pgProgWebusers) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps c";
        $query = $query . " where c.user = " . $pgProgWebusers->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
      /**
     * @return array
     */
    public function getPgCmdDwnldUsrRpsByFichierReponse($pgCmdFichiersRps) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps c";
        $query = $query . " where c.fichierReponse = " . $pgCmdFichiersRps->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

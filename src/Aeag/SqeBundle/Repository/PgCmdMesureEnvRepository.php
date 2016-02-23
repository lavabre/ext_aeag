<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdMesureEnvRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdMesureEnvRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPPgCmdMesureEnvs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdMesureEnv c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPPgCmdMesureEnvByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

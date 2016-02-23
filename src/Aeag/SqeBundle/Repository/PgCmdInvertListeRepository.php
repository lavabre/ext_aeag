<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertListeRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdInvertListes() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertListe c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertListeByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdInvertListe c";
        $query = $query . " where c.prelev = " . $pgCmdPrelevHbInvert->getPrelev() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
}

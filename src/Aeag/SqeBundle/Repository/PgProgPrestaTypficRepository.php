<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgPrestaTypficRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgPrestaTypficRepository extends EntityRepository {
    
    public function getPgProgPrestaTypficByPrestataire($prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.prestataire = " . $prestataire->getAdrCorId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.codeMilieu = '" . $pgProgTypeMilieu->getCodeMilieu() . "'";
        $query = $query . " and p.prestataire = " . $prestataire->getAdrCorId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
      public function getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $prestataire, $formatFic) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.codeMilieu = '" . $pgProgTypeMilieu->getCodeMilieu() . "'";
        $query = $query . " and p.prestataire = " . $prestataire->getAdrCorId() ;
        $query = $query . " and p.formatFic like '%" . $formatFic . "%'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
}

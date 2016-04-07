<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdAnalyseRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdAnalyseRepository extends EntityRepository {
    
     /**
     * @return array
     */
    public function getPgCmdAnalyses() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgCmdAnalyseByPrelevId($prelevId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $prelevId;
        $query = $query . " order by p.numOrdre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
      public function getNbCmdAnalyseSituByPrelev($pgCmdPrelev) {
        $query = "select count(p.prelevId)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and p.lieuAna = '1'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getSingleScalarResult();
     }
     
      public function getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, $statut) {
        $query = "select count(p.prelevId)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and p.lieuAna = '1'" ;
        $query = $query . " and  p.codeStatut = '" . $statut . "'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getSingleScalarResult();
     }
     
     public function getNbCmdAnalyseAnaByPrelev($pgCmdPrelev) {
        $query = "select count(p.prelevId)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and p.lieuAna = '2'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getSingleScalarResult();
     }
     
     public function getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, $statut) {
        $query = "select count(p.prelevId)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and p.lieuAna = '2'" ;
        $query = $query . " and  p.codeStatut = '" . $statut . "'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getSingleScalarResult();
     }
    
     public function getPgCmdAnalyseByPrelevIdNumOrdre($prelevId, $numOrdre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse p";
        $query = $query . " where p.prelevId = " . $prelevId;
        $query = $query  . " and p.numOrdre = " . $numOrdre;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
          return $qb->getOneOrNullResult();
    }
    
      /**
     * @return array
     */
    public function getPgCmdAnalyseByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse c";
        $query = $query . " where c.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and  c.paramProg = " .$pgProgLotParamAn->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getOneOrNullResult();
    }
    
     public function getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse c";
        $query = $query . " where c.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and  c.paramProg = " .$pgProgLotParamAn->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getResult();
    }
    
     public function getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $parametre, $numOrdre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse c";
        $query = $query . " where c.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and  c.codeParametre = '" . $parametre  . "'";
        $query = $query . " and  c.numOrdre = " . $numOrdre ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getOneOrNullResult();
    }
    
     public function getPgCmdAnalyseByPrelevCodeUniteParametre($pgCmdPrelev, $codeUnite, $parametre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdAnalyse c";
        $query = $query . " where c.prelevId = " . $pgCmdPrelev->getId() ;
        $query = $query . " and  c.codeUnite = '" . $codeUnite  . "'";
        $query = $query . " and  c.codeParametre <> '" . $parametre  . "'";
         $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
}

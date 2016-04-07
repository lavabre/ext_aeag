<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class gCmdMphytListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdPrelevRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdPrelevs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
      public function getPgCmdPrelevById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
          return $qb->getOneOrNullResult();
     }

     public function getPgCmdPrelevByPrestaPrel($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = " . $pgRefCorresPresta->getAdrCorid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
     public function getPgCmdPrelevByDemande($pgCmdDemande) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.demande = " . $pgCmdDemande->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
           
 public function getPgCmdPrelevByStation($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.station = " . $pgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }      
     
     public function getPgCmdPrelevByPeriode($pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.periode = " . $pgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }      

    public function getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgRefCorresPresta, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = " . $pgRefCorresPresta->getAdrCorid();
        $query = $query . " and p.demande = " . $pgCmdDemande->getId();
        $query = $query . " and p.station = " . $pgRefStationMesure->getOuvFoncId();
        $query = $query . " and p.periode = " . $pgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
          return $qb->getResult();
     }
    
}

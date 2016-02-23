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

     public function getPgCmdPrelevByPrestaPrel($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.prestaPrel = " . $pgRefCorresPresta->getAdrCorid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
     public function getPgCmdPrelevByDemande($pgCmdDemande) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.demande = " . $pgCmdDemande->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
           
 public function getPgCmdDemandeByStation($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.station = " . $pgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }      
     
     public function getPgCmdDemandeByPeriode($pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.periode = " . $pgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }      

    
    
}

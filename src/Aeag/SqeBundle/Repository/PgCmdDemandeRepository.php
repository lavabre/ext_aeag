<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdDemandeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdDemandeRepository extends EntityRepository {
    
     /**
     * @return array
     */
    public function getPgCmdDemandes() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " order by p.anneeProg, p.codeDemandeCmd";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgCmdDemandeById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getOneOrNullResult();
     }
     
     public function getPgCmdDemandeByCodeDemandeCmd($codeDemandeCmd) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.codeDemandeCmd = " . $codeDemandeCmd;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getOneOrNullResult();
     }
     
      public function getPgCmdDemandeByAnneeProg($anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.anneeProg = " . $anneeProg;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
      public function getPgCmdDemandeByCommanditaire($pgRefCorresProducteur) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.commanditaire = " . $pgRefCorresProducteur->getAdrCorid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
     public function getPgCmdDemandeByCommanditaireAnneeProg($pgRefCorresProducteur, $anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.commanditaire = " . $pgRefCorresProducteur->getAdrCorid();
        $query = $query . " and p.anneeProg = " . $anneeProg;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
       public function getPgCmdDemandeByPrestataire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.prestataire = " . $pgRefCorresPresta->getAdrCorid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
     
     
     
      public function getPgCmdDemandeByPrestataireAnneeProg($pgRefCorresPresta, $anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.prestataire = " . $pgRefCorresPresta->getAdrCorid();
        $query = $query . " and p.anneeProg = " . $anneeProg;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
     }
    
    public function getNbReponseByDemande($demande) {
        $query = "SELECT count(pean.periode)";
        $query .= " FROM Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgRefCorresPresta presta";
        $query .= " WHERE dmd.lotan = pean.lotan";
        $query .= " AND presta.adrCorId = dmd.prestataire";
        $query .= " AND pean.codeStatut <> 'INV'";
        $query .= " AND dmd.id = :demande";
        $query .= " GROUP BY pean.lotan";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demande);
        
        return $qb->getOneOrNullResult();

    }
}

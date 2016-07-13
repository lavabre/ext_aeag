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

    public function getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $query = $query . " and p.periode= " . $pgProgPeriodes->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $pgRefCorresPresta, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        if ($pgRefCorresPresta) {
            $query = $query . " and p.prestataire = " . $pgRefCorresPresta->getAdrCorid();
        }
        $query = $query . " and p.periode= " . $pgProgPeriodes->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function isPgCmdDemandesMarcheAeag($demandeId) {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgProgMarche m";
        $query .= " where lotan = dmd.lotan";
        $query .= " and lot = lotan.lot";
        $query .= " and m = lot.marche ";
        $query .= " and m.typeMarche = 'MOA'";
        $query .= " and dmd.id = :demande";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByLotan($pgProgLotAn) {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        $query .= " where lotan = dmd.lotan";
        $query .= " and lotan = pean.lotan";
        $query .= " and pean.periode = dmd.periode";
        $query .= " and pean.codeStatut <> 'INV'";
        $query .= " and lotan.id = :lotan";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgCmdDemandeForRelance7JAvt() {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd";
        $query .= " join dmd.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " join dmd.periode periode";
        $query .= " join lot.codeMilieu milieu";
        $query .= " left join Aeag\SqeBundle\Entity\PgCmdFichiersRps rps with dmd.id = rps.demande where rps.demande is null";
        $query .= " and DATE_ADD(periode.dateDeb, (lot.delaiLot + lot.delaiPrel), 'day') = DATE_SUB(CURRENT_TIMESTAMP(), 7, 'day')";
        $query .= " and milieu.codeMilieu like '%PC'";
        $query .= " order by dmd.id";
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }
    
    public function getPgCmdDemandeForRelance1JAprs() {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd";
        $query .= " join dmd.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " join dmd.periode periode";
        $query .= " join lot.codeMilieu milieu";
        $query .= " left join Aeag\SqeBundle\Entity\PgCmdFichiersRps rps with dmd.id = rps.demande where rps.demande is null";
        $query .= " and DATE_ADD(periode.dateDeb, (lot.delaiLot + lot.delaiPrel), 'day') = DATE_ADD(CURRENT_TIMESTAMP(), 1, 'day')";
        $query .= " and milieu.codeMilieu like '%PC'";
        $query .= " order by dmd.id";
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

}

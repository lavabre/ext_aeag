<?php

/**
 * Description of PgProgLotAnRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotAnRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " order by p.anneeProg, p_version";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotAnByanneeProg($anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        if ($anneeProg) {
            $query = $query . " where p.anneeProg = :anneeProg";
        }
        $query = $query . " order by p.anneeProg, p.lot, p.version";
        $qb = $this->_em->createQuery($query);
        if ($anneeProg) {
            $qb->setParameter('anneeProg', $anneeProg);
        }
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByLot($pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.lot = :pgProgLot";
        $query = $query . " order by p.anneeProg, p.version";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByAnneeLot($annee, $pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = :annee";
        $query = $query . " and p.lot = :pgProgLot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByLotVersion($pgProgLot, $version) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.lot = :pgProgLot";
        $query = $query . " and p.version = :version";
        $query = $query . " order by p.anneeProg, p.version";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        $qb->setParameter('version', $version);
        // print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByAnneeLotVersion($annee, $pgProgLot, $version) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = :annee";
        $query = $query . " and p.lot = :pgProgLot";
        $query = $query . " and p.version = :version";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        $qb->setParameter('version', $version);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getMaxVersionByAnneeLot($annee, $pgProgLot) {
        $query = "select max(p.version)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = :annee";
        $query = $query . " and p.lot = :pgProgLot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgProgLotAnByPresta($user, $codeMilieu = null) {

        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotParamAn paran, Aeag\SqeBundle\Entity\PgRefCorresPresta presta, Aeag\SqeBundle\Entity\PgProgWebusers users, Aeag\SqeBundle\Entity\PgProgLotGrparAn gran, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu";
        $query .= " where paran.prestataire = presta.adrCorId";
        $query .= " and users.prestataire = presta.adrCorId";
        $query .= " and gran.id = paran.grparan";
        $query .= " and lotan.id = gran.lotan";
        $query .= " and lotan.lot = lot.id";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and lot.codeMilieu = milieu.codeMilieu";
        $query .= " and users.extId = :aeagUser";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and pean.codeStatut <> 'INV'";

        if (!is_null($codeMilieu)) {
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }


        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aeagUser', $user->getId()); // Id de l'utilisateur

        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByPrestaAlt($user, $codeMilieu = null) {

        $query = "select lot.id, lotan.annee_prog";
        $query .= " from pg_prog_lot_an lotan";
        $query .= " join pg_prog_lot lot on lotan.lot_id = lot.id";
        $query .= " join pg_cmd_demande dmd on dmd.lotan_id = lotan.id";
        $query .= " join pg_prog_lot_periode_an pean on pean.lotan_id= lotan.id";
        $query .= " join pg_prog_type_milieu milieu on lot.code_milieu = milieu.code_milieu";
        $query .= " join pg_prog_lot_grpar_an gran on lotan.id = gran.lotan_id";
        $query .= " join pg_prog_lot_param_an paran on gran.id = paran.grparan_id";
        $query .= " join pg_ref_corres_presta presta on paran.prestataire_id = presta.adr_cor_id";
        $query .= " join pg_prog_webusers users on users.presta_id = presta.adr_cor_id";
        $query .= " where lotan.code_statut <> 'INV'";
        $query .= " and (lotan.phase_id >= 5 and lotan.phase_id <= 8)";
        $query .= " and users.ext_id = :aeagUser";
        $query .= " and pean.code_statut <> 'INV'";

        if (!is_null($codeMilieu)) {
            $query .= " and milieu.code_milieu LIKE :codemilieu";
        }
        $query .= " group by lot.id, lotan.annee_prog";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('aeagUser', $user->getId());
        if (!is_null($codeMilieu)) {
            $stmt->bindValue('codemilieu', '%' . $codeMilieu);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPgProgLotAnSaisieDonneesByPresta($user, $codeMilieu = null) {

        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotParamAn paran,";
        $query .= "         Aeag\SqeBundle\Entity\PgRefCorresPresta presta, ";
        $query .= "         Aeag\SqeBundle\Entity\PgProgWebusers users,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotGrparAn gran,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn lotan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean,";
        if ($codeMilieu) {
            $query .= "         Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu,";
        }
        $query .= "          Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where paran.prestataire = presta.adrCorId";
        $query .= " and users.prestataire = presta.adrCorId";
        $query .= " and gran.id = paran.grparan";
        $query .= " and lotan.id = gran.lotan";
        $query .= " and lotan.lot = lot.id";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and users.extId = :aeagUser";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and pean.codeStatut <> 'INV'";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = presta.adrCorId";
        $query .= " and typFic.formatFic like '%Saisie%'";
        if (!is_null($codeMilieu)) {
            $query .= " and lot.codeMilieu = milieu.codeMilieu";
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aeagUser', $user->getId()); // Id de l'utilisateur

        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnSuiviByPrestaPrel($user) {

        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotParamAn paran, ";
        $query .= "Aeag\SqeBundle\Entity\PgRefCorresPresta presta, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgWebusers users, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgGrpParamRef grref, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgLotGrparAn gran, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgLotAn lotan, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgLot lot, ";
        $query .= "Aeag\SqeBundle\Entity\PgCmdDemande dmd, ";
        $query .= "Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        $query .= " where paran.prestataire = presta.adrCorId";
        $query .= " and users.prestataire = presta.adrCorId";
        $query .= " and grref.id = gran.grparRef";
        $query .= " and (grref.typeGrp = 'ENV' or grref.typeGrp = 'SIT')";
        $query .= " and gran.id = paran.grparan";
        $query .= " and lotan.id = gran.lotan";
        $query .= " and lotan.lot = lot.id";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and users.extId = :aeagUser";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and pean.codeStatut <> 'INV'";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aeagUser', $user->getId()); // Id de l'utilisateur
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByAdmin($codeMilieu = null) {
        $query = "select distinct lotan";
        if ($codeMilieu) {
            $query .= " from Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu";
        } else {
            $query .= " from Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        }
        $query .= " where lotan.lot = lot.id";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and pean.codeStatut <> 'INV'";

        if (!is_null($codeMilieu)) {
            $query .= " and lot.codeMilieu = milieu.codeMilieu";
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }

        $qb = $this->_em->createQuery($query);
        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }

        return $qb->getResult();
    }

    public function getPgProgLotAnByAdminAlt($codeMilieu = null) {

        $query = "select lot.id, lotan.annee_prog";
        $query .= " from pg_prog_lot_an lotan";
        $query .= " join pg_prog_lot lot on lotan.lot_id = lot.id";
        $query .= " join pg_cmd_demande dmd on dmd.lotan_id = lotan.id";
        $query .= " join pg_prog_lot_periode_an pean on pean.lotan_id= lotan.id";
        $query .= " join pg_prog_type_milieu milieu on lot.code_milieu = milieu.code_milieu";
        $query .= " where lotan.code_statut <> 'INV'";
        $query .= " and (lotan.phase_id >= 5 and lotan.phase_id <= 8)";
        $query .= " and pean.code_statut <> 'INV'";

        if (!is_null($codeMilieu)) {
            $query .= " and milieu.code_milieu LIKE :codemilieu";
        }
        $query .= " group by lot.id, lotan.annee_prog";

        $stmt = $this->_em->getConnection()->prepare($query);
        if (!is_null($codeMilieu)) {
            $stmt->bindValue('codemilieu', '%' . $codeMilieu);
        }
        //print_r($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPgProgLotAnSaisieDonneesByAdmin($codeMilieu = null) {

        $query = "select distinct ltan";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotStationAn stan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean,";
        if ($codeMilieu) {
            $query .= "         Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu,";
        }
        $query .= "          Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where ltan.codeStatut <> 'INV'";
        $query .= " and ltan.phase >= 5 and ltan.phase <= 8";
        $query .= " and ltan.id = stan.lotan";
        $query .= " and ltan.id = pean.lotan";
        $query .= " and pean.codeStatut !='INV'";
        $query .= " and lot.id = ltan.lot";
        $query .= " and ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = prel.prestaPrel";
        $query .= " and typFic.formatFic like '%Saisie%'";
        $query .= " and  prel.station = stan.station";
        $query .= " and prel.periode = pean.periode";
        if (!is_null($codeMilieu)) {
            $query .= " and lot.codeMilieu = milieu.codeMilieu";
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }
        $qb = $this->_em->createQuery($query);
        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnSaisieDonneesByAdmin1($codeMilieu = null) {

        $query = "select distinct ltan";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLot lot,";
        if ($codeMilieu) {
            $query .= "         Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu,";
        }
        $query .= "          Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where ltan.codeStatut <> 'INV'";
        $query .= " and ltan.phase >= 5 and ltan.phase <= 8";
        $query .= " and lot.id = ltan.lot";
        $query .= " and ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = prel.prestaPrel";
        $query .= " and typFic.formatFic like '%Saisie%'";
        if (!is_null($codeMilieu)) {
            $query .= " and lot.codeMilieu = milieu.codeMilieu";
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }
        $qb = $this->_em->createQuery($query);
        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByAdmin1($codeMilieu = null) {
        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu";
        $query .= " where lotan.lot = lot.id";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and lot.codeMilieu = milieu.codeMilieu";
        $query .= " and lotan.codeStatut <> 'INV'";
        // $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and pean.codeStatut <> 'INV'";
        if (!is_null($codeMilieu)) {
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }

        $qb = $this->_em->createQuery($query);

        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }

        return $qb->getResult();
    }

    public function getPgProgLotAnByProg($user, $codeMilieu = null) {
        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgMarcheUser mu, Aeag\SqeBundle\Entity\PgProgWebusers users, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLot lot, Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu";
        $query .= " where lotan.lot = lot.id";
        $query .= " and mu.marche = lot.marche";
        $query .= " and users.id = mu.webuser";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and lot.codeMilieu = milieu.codeMilieu";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and users.extId = :aeagUser";
        $query .= " and pean.codeStatut <> 'INV'";
        if (!is_null($codeMilieu)) {
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aeagUser', $user->getId()); // Id de l'utilisateur

        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }

        return $qb->getResult();
    }

    public function getPgProgLotAnSaisieDonneesByProg($user, $codeMilieu = null) {
        $query = "select distinct lotan";
        $query .= " from Aeag\SqeBundle\Entity\PgProgMarcheUser mu,";
        $query .= " Aeag\SqeBundle\Entity\PgProgWebusers users,";
        $query .= " Aeag\SqeBundle\Entity\PgProgLotAn lotan,";
        $query .= " Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query .= " Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= " Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean,";
        if ($codeMilieu) {
            $query .= "         Aeag\SqeBundle\Entity\PgProgTypeMilieu milieu,";
        }
        $query .= " Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where lotan.lot = lot.id";
        $query .= " and mu.marche = lot.marche";
        $query .= " and users.id = mu.webuser";
        $query .= " and dmd.lotan = lotan.id";
        $query .= " and pean.lotan = lotan.id";
        $query .= " and lotan.codeStatut <> 'INV'";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = users.prestataire";
        $query .= " and typFic.formatFic like '%Saisie%'";

        if (!is_null($codeMilieu)) {
            $query .= " and lot.codeMilieu = milieu.codeMilieu";
            $query .= " and milieu.codeMilieu LIKE :codemilieu";
        }
        $query .= " and lotan.phase >= 5 and lotan.phase <= 8";
        $query .= " and users.extId = :aeagUser";
        $query .= " and pean.codeStatut <> 'INV'";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aeagUser', $user->getId()); // Id de l'utilisateur

        if (!is_null($codeMilieu)) {
            $qb->setParameter('codemilieu', '%' . $codeMilieu);
        }

        return $qb->getResult();
    }

    public function getPgProgLotAnByProgAlt($user, $codeMilieu = null) {

        $query = "select lot.id, lotan.annee_prog";
        $query .= " from pg_prog_lot_an lotan";
        $query .= " join pg_prog_lot lot on lotan.lot_id = lot.id";
        $query .= " join pg_cmd_demande dmd on dmd.lotan_id = lotan.id";
        $query .= " join pg_prog_lot_periode_an pean on pean.lotan_id= lotan.id";
        $query .= " join pg_prog_type_milieu milieu on lot.code_milieu = milieu.code_milieu";
        $query .= " join pg_prog_marche_user mu on mu.marche_id = lot.marche_id";
        $query .= " join pg_prog_webusers users on users.id = mu.webuser_id";
        $query .= " where lotan.code_statut <> 'INV'";
        $query .= " and (lotan.phase_id >= 5 and lotan.phase_id <= 8)";
        $query .= " and users.ext_id = :aeagUser";
        $query .= " and pean.code_statut <> 'INV'";

        if (!is_null($codeMilieu)) {
            $query .= " and milieu.code_milieu LIKE :codemilieu";
        }
        $query .= " group by lot.id, lotan.annee_prog";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('aeagUser', $user->getId());
        if (!is_null($codeMilieu)) {
            $stmt->bindValue('codemilieu', '%' . $codeMilieu);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getPgProgLotAnDistinctAnnee() {
        $query = "select distinct p.anneeProg";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getProgrammationPeriodes($lotanId) {
        $query = "select * from sqe_programmation_periode(:lotanId)";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotanId', $lotanId);
        return $qb->getResult();
//        return $this->getEntityManager('')
//                        ->getConnection()
//                        ->query($query);
    }

    /**
     * @return array
     */
    public function getProgrammationStationPeriodes($lotanId, $stationId) {
        $query = "select * from sqe_programmation_station_periode(:lotanId , :stationId)";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotanId', $lotanId);
        $qb->setParameter('stationId', $stationId);
        return $qb->getResult();
//        return $this->getEntityManager('')
//                        ->getConnection()
//                        ->query($query);
    }

}

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
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgCmdDemandeByCodeDemandeCmd($codeDemandeCmd) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.codeDemandeCmd = :codeDemandeCmd";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeDemandeCmd', $codeDemandeCmd);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgCmdDemandeByAnneeProg($anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.anneeProg = :anneeProg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('anneeProg', $anneeProg);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByCommanditaire($pgRefCorresProducteur) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.commanditaire = :pgRefCorresProducteur";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresProducteur', $pgRefCorresProducteur->getAdrCorid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByCommanditaireAnneeProg($pgRefCorresProducteur, $anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.commanditaire = :pgRefCorresProducteur";
        $query = $query . " and p.anneeProg = :anneeProg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresProducteur', $pgRefCorresProducteur->getAdrCorid());
        $qb->setParameter('anneeProg', $anneeProg);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByPrestataire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.prestataire = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByPrestataireAnneeProg($pgRefCorresPresta, $anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.prestataire = :pgRefCorresPresta";
        $query = $query . " and p.anneeProg = :anneeProg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        $qb->setParameter('anneeProg', $anneeProg);
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
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.periode= :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $pgRefCorresPresta, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDemande p";
        $query = $query . " where p.lotan =:pgProgLotAn";
        if ($pgRefCorresPresta) {
            $query = $query . " and p.prestataire = :pgRefCorresPresta";
        }
        $query = $query . " and p.periode= :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getid());
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

    public function getPgCmdDemandeByLotans(array $pgProgLotAns) {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        $query .= " where lotan = dmd.lotan";
        $query .= " and lotan = pean.lotan";
        $query .= " and pean.periode = dmd.periode";
        $query .= " and pean.codeStatut <> 'INV'";
        $query .= " and lotan.id IN (:lotans)";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotans', $pgProgLotAns);
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
        $query .= " join lot.marche marche";
        $query .= " join dmd.phaseDemande phase";
        $query .= " left join Aeag\SqeBundle\Entity\PgCmdFichiersRps rps with dmd.id = rps.demande where rps.demande is null";
        //$query .= " and DATE_ADD(periode.dateDeb, (COALESCE(lot.delaiLot, 30) + COALESCE(lot.delaiPrel, 7)), 'day') = DATE_ADD(CURRENT_TIMESTAMP(), 7, 'day')";
        $query .= " and DATE_DIFF(CURRENT_DATE(), periode.dateDeb) = (COALESCE(lot.delaiLot, 30) + COALESCE(lot.delaiPrel, 7) + 7)";
        $query .= " and milieu.codeMilieu like '%PC'";
        $query .= " and marche.typeMarche = 'MOA'";
        $query .= " and phase.codePhase NOT IN ('D40','D50')";
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
        $query .= " join lot.marche marche";
        $query .= " join dmd.phaseDemande phase";
        $query .= " left join Aeag\SqeBundle\Entity\PgCmdFichiersRps rps with dmd.id = rps.demande where rps.demande is null";
        //$query .= " and DATE_ADD(periode.dateDeb, (COALESCE(lot.delaiLot, 30) + COALESCE(lot.delaiPrel, 7)), 'day') = DATE_SUB(CURRENT_TIMESTAMP(), 1, 'day')";
        $query .= " and DATE_DIFF(CURRENT_DATE(), periode.dateDeb) = (COALESCE(lot.delaiLot, 30) + COALESCE(lot.delaiPrel, 7) - 1)";
        $query .= " and milieu.codeMilieu like '%PC'";
        $query .= " and marche.typeMarche = 'MOA'";
        $query .= " and phase.codePhase NOT IN ('D40','D50')";
        $query .= " order by dmd.id";
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    public function getCountPgCmdDemandeByLotan($pgProgLotAn) {
        $query = "select count(dmd)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        $query .= " where lotan = dmd.lotan";
        $query .= " and lotan = pean.lotan";
        $query .= " and pean.periode = dmd.periode";
        $query .= " and pean.codeStatut <> 'INV'";
        $query .= " and lotan.id = :lotan";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getid());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getCountPgCmdDemandeByLotanPhase($pgProgLotAn, $pgProgPhase) {
        $query = "select count(dmd)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotAn lotan, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean";
        $query .= " where lotan = dmd.lotan";
        $query .= " and lotan = pean.lotan";
        $query .= " and pean.periode = dmd.periode";
        $query .= " and pean.codeStatut <> 'INV'";
        $query .= " and lotan.id = :lotan";
        $query .= " and dmd.phaseDemande = :phase";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getId());
        $qb->setParameter('phase', $pgProgPhase->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getDonneesBrutesAnalyseByDemande($pgCmdDemande) {

        $query = '(select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau",
                    prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur",
                    prlv.date_prelev as "Date-heure du prélèvement", ana.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
                    param.nom_parametre as "Nom paramètre", case when prlvpc.zone_verticale = \'9\' then \'6\' else prlvpc.zone_verticale end as "Zone verticale", prlvpc.profondeur as "Profondeur",
                    prlv.code_support as "Code Support", sup.nom_support as "Nom Support", ana.code_fraction as "Code Fraction", frac.nom_fraction as "Nom Fraction", ana.code_methode as "Code Méthode", meth.nom_methode as "Nom Méthode",
                    ana.code_remarque as "Code Remarque", ana.resultat as "Resultat", \'\' as "Valeur textuelle", ana.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
                    unit.symbole as "Symbole Unité", ana.lq_ana as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau",
                    resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", ana.commentaire as "Commentaire"
                    from pg_cmd_prelev prlv
                    join pg_cmd_demande dmd on prlv.demande_id = dmd.id
                    join pg_ref_station_mesure msr on msr.ouv_fonc_id = prlv.station_id
                    join pg_ref_corres_presta presta on presta.adr_cor_id = prlv.presta_prel_id
                    join pg_cmd_analyse ana on ana.prelev_id = prlv.id
                    join pg_sandre_parametres param on ana.code_parametre = param.code_parametre
                     join pg_cmd_prelev_pc prlvpc on prlvpc.prelev_id = ana.prelev_id and prlvpc.num_ordre = ana.num_ordre
                    join pg_sandre_supports sup on sup.code_support = prlv.code_support
                    join pg_sandre_fractions frac on frac.code_fraction = ana.code_fraction
                    left join pg_sandre_methodes meth on meth.code_methode = ana.code_methode
                    join pg_sandre_unites unit on unit.code_unite = ana.code_unite
                    join pg_ref_corres_presta presta2 on presta2.adr_cor_id = dmd.prestataire_id
                    join pg_prog_lot_station_an station on station.lotan_id = dmd.lotan_id and station.station_id = prlv.station_id
                    join pg_ref_reseau_mesure resmes on resmes.groupement_id = station.rsx_id
                    join pg_prog_lot_an lotan on dmd.lotan_id = lotan.id
                    join pg_prog_lot lot on lot.id = lotan.lot_id
                    join pg_prog_marche marche on marche.id = lot.marche_id
                    join pg_ref_corres_producteur prod on prod.adr_cor_id = marche.resp_adr_cor_id
                    where dmd.id = :demande)';

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('demande', $pgCmdDemande->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getLotanAndPrestaByCodePhase($codePhase) {
        $query = "select prestataire.adrCorId as prestaId, lotan.id as lotanId";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdDemande dmd";
        $query .= " join dmd.phaseDemande phase";
        $query .= " join dmd.prestataire prestataire";
        $query .= " join dmd.lotan lotan";
        $query .= " where phase.codePhase = :codePhase";
        $query .= " group by prestataire.adrCorId, lotan.id";
        //$query .= " order by lotan";
        /*select dmd.lotan_id, dmd.prestataire_id
            from pg_cmd_demande dmd
            where phase_dmd_id = 110
            group by dmd.lotan_id, dmd.prestataire_id
            order by dmd.lotan_id asc*/
        $qb = $this->_em->createQuery($query);
        
        $qb->setParameter('codePhase', $codePhase);
        
        return $qb->getResult();
    }

}

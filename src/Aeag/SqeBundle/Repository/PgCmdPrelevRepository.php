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
    
    public function getPgCmdPrelevByCodePrelevCodeDmdAndPhase($pgCmdPrelev, $pgCmdDemande, $pgProgPhase) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query .= " and p.demande = :demande";
        $query .= " and p.codePrelevCmd = :codeprelev";
        $query .= " and p.phaseDmd >= :phase";
        $query .= " and p.fichierRps IS NOT NULL";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $pgCmdDemande);
        $qb->setParameter('codeprelev', $pgCmdPrelev->getCodePrelevCmd());
        $qb->setParameter('phase', $pgProgPhase);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getCountPgCmdPrelevByPhase($pgCmdDemande, $pgProgPhase) {
        $query = "select count(p)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query .= " and p.demande = :demande";
        $query .= " and p.phaseDmd = :phase";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $pgCmdDemande);
        $qb->setParameter('phase', $pgProgPhase);
        
        return $qb->getResult();
    }
    
    public function getCountAllPgCmdPrelev($pgCmdDemande) {
        $query = "select count(p)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query .= " and p.demande = :demande";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $pgCmdDemande);
        
        return $qb->getResult();
    }
    
    public function getDonneesBrutes($pgCmdFichierRps) {
        return array_merge($this->getDonneesBrutesAnalyse($pgCmdFichierRps), $this->getDonneesBrutesMesureEnv($pgCmdFichierRps));
    }
    
    public function getDonneesBrutesAnalyse($pgCmdFichierRps) {

        $query = '(select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau", 
            prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur", 
            prlv.date_prelev as "Date-heure du prélèvement", ana.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
            param.nom_parametre as "Nom paramètre", prlvpc.zone_verticale as "Zone verticale", prlvpc.profondeur as "Profondeur", prlv.code_support as "Code Support", 
            sup.nom_support as "Nom Support", ana.code_fraction as "Code Fraction", frac.nom_fraction as "Nom Fraction", ana.code_methode as "Code Méthode", meth.nom_methode as "Nom Méthode",
            ana.code_remarque as "Code Remarque", ana.resultat as "Resultat", \'\' as "Valeur textuelle", ana.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
            unit.symbole as "Symbole Unité", ana.lq_ana as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau", 
            resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", \'\' as "Commentaire"
            from pg_cmd_demande dmd, pg_cmd_prelev prlv, pg_ref_station_mesure msr, pg_ref_corres_presta presta, pg_cmd_analyse ana, pg_sandre_parametres param, pg_cmd_prelev_pc prlvpc, pg_sandre_supports sup,
            pg_sandre_fractions frac, pg_sandre_methodes meth, pg_sandre_unites as unit, pg_ref_corres_presta presta2, pg_ref_reseau_mesure resmes, pg_prog_lot_station_an station, pg_ref_corres_producteur prod, 
            pg_prog_marche marche, pg_prog_lot lot, pg_prog_lot_an lotan
            where prlv.demande_id = dmd.id
            and msr.ouv_fonc_id = prlv.station_id
            and presta.adr_cor_id = prlv.presta_prel_id
            and ana.prelev_id = prlv.id
            and ana.code_parametre = param.code_parametre
            and prlvpc.prelev_id = prlv.id
            and sup.code_support = prlv.code_support
            and frac.code_fraction = ana.code_fraction
            and meth.code_methode = ana.code_methode
            and unit.code_unite = ana.code_unite
            and presta2.adr_cor_id = dmd.prestataire_id
            and resmes.groupement_id = station.rsx_id
            and station.lotan_id = dmd.lotan_id 
            and station.station_id = prlv.station_id
            and prod.adr_cor_id = marche.resp_adr_cor_id
            and marche.id = lot.marche_id 
            and lot.id = lotan.lot_id 
            and dmd.lotan_id = lotan.id
            and prlv.fichier_rps_id = :fichier)';
        
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('fichier', $pgCmdFichierRps->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getDonneesBrutesMesureEnv($pgCmdFichierRps) {
        $query = '(select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau", 
            prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur", 
            prlv.date_prelev as "Date-heure du prélèvement", mesenv.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
            param.nom_parametre as "Nom paramètre", prlvpc.zone_verticale as "Zone verticale", prlvpc.profondeur as "Profondeur", prlv.code_support as "Code Support", 
            sup.nom_support as "Nom Support", \'\' as "Code Fraction", \'\' as "Nom Fraction", \'\' as "Code Méthode", \'\' as "Nom Méthode",
            mesenv.code_remarque as "Code Remarque", mesenv.resultat as "Resultat", \'\' as "Valeur textuelle", mesenv.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
            unit.symbole as "Symbole Unité", 0 as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau", 
            resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", \'\' as "Commentaire"
            from pg_cmd_demande dmd, pg_cmd_prelev prlv, pg_ref_station_mesure msr, pg_ref_corres_presta presta, pg_cmd_mesure_env mesenv, pg_sandre_parametres param, pg_cmd_prelev_pc prlvpc, pg_sandre_supports sup,
            pg_sandre_methodes meth, pg_sandre_unites as unit, pg_ref_corres_presta presta2, pg_ref_reseau_mesure resmes, pg_prog_lot_station_an station, pg_ref_corres_producteur prod, 
            pg_prog_marche marche, pg_prog_lot lot, pg_prog_lot_an lotan
            where prlv.demande_id = dmd.id
            and msr.ouv_fonc_id = prlv.station_id
            and presta.adr_cor_id = prlv.presta_prel_id
            and mesenv.prelev_id = prlv.id
            and mesenv.code_parametre = param.code_parametre
            and prlvpc.prelev_id = prlv.id
            and sup.code_support = prlv.code_support
            and meth.code_methode = mesenv.code_methode
            and unit.code_unite = mesenv.code_unite
            and presta2.adr_cor_id = dmd.prestataire_id
            and resmes.groupement_id = station.rsx_id
            and station.lotan_id = dmd.lotan_id 
            and station.station_id = prlv.station_id
            and prod.adr_cor_id = marche.resp_adr_cor_id
            and marche.id = lot.marche_id 
            and lot.id = lotan.lot_id 
            and dmd.lotan_id = lotan.id
            and prlv.fichier_rps_id = :fichier)';
        
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('fichier', $pgCmdFichierRps->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

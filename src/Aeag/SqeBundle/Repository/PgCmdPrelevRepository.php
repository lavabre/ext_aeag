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
    
     public function getPgCmdPrelevBySupport($pgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.codeSupport = '" . $pgSandreSupports->getCodeSupport() . "'";
         $query = $query . " order by p.station,  p.periode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgCmdPrelevBySyntheseSupport($pgSandreSupports) {
       $query = " SELECT prelev.id as prelevId, support.nomSupport, station.ouvFoncId, suiviprel.id as suiviPrelId ";
       $query = $query . "FROM Aeag\SqeBundle\Entity\PgCmdPrelev prelev,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgCmdSuiviPrel suiviprel,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgCmdDemande demande,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgRefStationMesure station,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgProgLotAn lotan,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgProgLot lot,";
       $query = $query . "           Aeag\SqeBundle\Entity\PgProgTypeMilieu typemilieu, ";
        $query = $query . "           Aeag\SqeBundle\Entity\PgSandreSupports support ";
       $query = $query . " where support.codeSupport = '" . $pgSandreSupports->getCodeSupport() . "'";
       $query = $query . "and support.codeSupport= prelev.codeSupport  ";
       $query = $query . "and demande.id = prelev.demande ";
       $query = $query . "and station.ouvFoncId = prelev.station ";
       $query = $query . "and lotan.id = demande.lotan ";
       $query = $query . "and lotan.phase <> 9 ";
       $query = $query . "and lot.id = lotan.lot ";
       $query = $query . "and typemilieu.codeMilieu = lot.codeMilieu ";
       $query = $query . "and (substring(typemilieu.codeMilieu,2,2) = 'HB' or typemilieu.codeMilieu = 'RHM') ";
       $query = $query . "and prelev.id = suiviprel.prelev ";
       $query = $query . "and suiviprel.id = (select max(ss.id) from Aeag\SqeBundle\Entity\PgCmdSuiviPrel ss where ss.prelev = prelev.id) ";
       $query = $query . "order by prelev.station, prelev.periode ";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgCmdPrelevBySupportPresta($pgSandreSupports, $pgProgWebUser) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.codeSupport = " . $pgSandreSupports->getCodeSupport();
        $query = $query . "  and presta_prel_id = '" . $pgProgWebUser->getPrestataire() . "'";
         $query = $query . " order by p.station,  p.periode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

      public function getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.demande = " . $pgCmdDemande->getId();
        $query = $query . " and p.station = " . $pgRefStationMesure->getOuvFoncId();
        $query = $query . " and p.periode = " . $pgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
      public function getPgCmdPrelevByPrestaPrelDemandePeriode($pgRefCorresPresta, $pgCmdDemande,  $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = " . $pgRefCorresPresta->getAdrCorid();
        $query = $query . " and p.demande = " . $pgCmdDemande->getId();
         $query = $query . " and p.periode = " . $pgProgPeriodes->getId();
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
    
      public function getPgCmdPrelevUniqueByPrestaPrelDemandeStationPeriode($pgRefCorresPresta, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = " . $pgRefCorresPresta->getAdrCorid();
        $query = $query . " and p.demande = " . $pgCmdDemande->getId();
        $query = $query . " and p.station = " . $pgRefStationMesure->getOuvFoncId();
        $query = $query . " and p.periode = " . $pgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgCmdPrelevByCodePrelevCodeDmdAndPhase($pgCmdPrelev, $pgCmdDemande, $pgProgPhase) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query .= " where p.demande = :demande";
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
        $query .= " where p.demande = :demande";
        $query .= " and p.phaseDmd = :phase";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $pgCmdDemande);
        $qb->setParameter('phase', $pgProgPhase);

        return $qb->getResult();
    }

    public function getCountAllPgCmdPrelev($pgCmdDemande) {
        $query = "select count(p)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query .= " where p.demande = :demande";
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
                    param.nom_parametre as "Nom paramètre", case when prlvpc.zone_verticale = \'9\' then \'6\' else prlvpc.zone_verticale end as "Zone verticale", prlvpc.profondeur as "Profondeur", prlv.code_support as "Code Support", 
                    sup.nom_support as "Nom Support", ana.code_fraction as "Code Fraction", frac.nom_fraction as "Nom Fraction", ana.code_methode as "Code Méthode", meth.nom_methode as "Nom Méthode",
                    ana.code_remarque as "Code Remarque", ana.resultat as "Resultat", \'\' as "Valeur textuelle", ana.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
                    unit.symbole as "Symbole Unité", ana.lq_ana as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau", 
                    resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", \'\' as "Commentaire"
                    from pg_cmd_prelev prlv
                    join pg_cmd_demande dmd on prlv.demande_id = dmd.id
                    join pg_ref_station_mesure msr on msr.ouv_fonc_id = prlv.station_id
                    join pg_ref_corres_presta presta on presta.adr_cor_id = prlv.presta_prel_id
                    join pg_cmd_analyse ana on ana.prelev_id = prlv.id
                    join pg_sandre_parametres param on ana.code_parametre = param.code_parametre
                    join pg_cmd_prelev_pc prlvpc on prlvpc.prelev_id = prlv.id
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
                    where prlv.fichier_rps_id = :fichier)';
        
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('fichier', $pgCmdFichierRps->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDonneesBrutesMesureEnv($pgCmdFichierRps) {
        $query = '(select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau", 
                    prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur", 
                    prlv.date_prelev as "Date-heure du prélèvement", mesenv.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
                    param.nom_parametre as "Nom paramètre", case when prlvpc.zone_verticale = \'9\' then \'6\' else prlvpc.zone_verticale end as "Zone verticale", prlvpc.profondeur as "Profondeur", prlv.code_support as "Code Support", 
                    sup.nom_support as "Nom Support", \'\' as "Code Fraction", \'\' as "Nom Fraction", \'\' as "Code Méthode", \'\' as "Nom Méthode",
                    mesenv.code_remarque as "Code Remarque", mesenv.resultat as "Resultat", \'\' as "Valeur textuelle", mesenv.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
                    unit.symbole as "Symbole Unité", 0 as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau", 
                    resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", \'\' as "Commentaire"
                    from pg_cmd_prelev prlv
                    join pg_cmd_demande dmd on prlv.demande_id = dmd.id
                    join pg_ref_station_mesure msr on msr.ouv_fonc_id = prlv.station_id
                    join pg_ref_corres_presta presta on presta.adr_cor_id = prlv.presta_prel_id
                    join pg_cmd_mesure_env mesenv on mesenv.prelev_id = prlv.id
                    join pg_sandre_parametres param on param.code_parametre = mesenv.code_parametre
                    join pg_cmd_prelev_pc prlvpc on prlvpc.prelev_id = prlv.id
                    join pg_sandre_supports sup on sup.code_support = prlv.code_support
                    left join pg_sandre_methodes meth on meth.code_methode = mesenv.code_methode
                    join pg_sandre_unites unit on unit.code_unite = mesenv.code_unite
                    join pg_ref_corres_presta presta2 on presta2.adr_cor_id = dmd.prestataire_id
                    join pg_prog_lot_station_an station on station.lotan_id = dmd.lotan_id and station.station_id = prlv.station_id
                    join pg_ref_reseau_mesure resmes on resmes.groupement_id = station.rsx_id
                    join pg_prog_lot_an lotan on dmd.lotan_id = lotan.id
                    join pg_prog_lot lot on lot.id = lotan.lot_id
                    join pg_prog_marche marche on marche.id = lot.marche_id
                    join pg_ref_corres_producteur prod on prod.adr_cor_id = marche.resp_adr_cor_id
                    where prlv.fichier_rps_id = :fichier)';
        
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('fichier', $pgCmdFichierRps->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAutrePrelevs($pgCmdPrelev) {
        $query = "select max(sp.datePrel) as datePrel, sup.codeSupport as codeSupport, sup.nomSupport as support";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . " , Aeag\SqeBundle\Entity\PgCmdSuiviPrel sp ";
        $query = $query . " ,Aeag\SqeBundle\Entity\PgSandreSupports sup";
        $query = $query . " where prl.station = " . $pgCmdPrelev->getStation()->getOuvFoncId();
        $query = $query . " and prl.codeSupport in ('4','10','11','13','27')";
        $query = $query . " and prl.codeSupport <> '" . $pgCmdPrelev->getCodeSupport()->getCodeSupport() . "'";
        $query = $query . " and sup.codeSupport = prl.codeSupport";
        $query = $query . " and prl.id = sp.prelev";
        $query = $query . " and sp.statutPrel = 'P'";
        $query = $query . " and sp.validation <> 'R'";
        $query = $query . " group by sp.datePrel, sup.codeSupport, sup.nomSupport";
        $query = $query . " order by sp.datePrel desc";
        $qb = $this->_em->createQuery($query);
//        if ($pgCmdPrelev->getStation()->getOuvFoncId() == 557655){
//        print_r($query);
//        }
        return $qb->getResult();
    }

}

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
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgCmdPrelevByPrestaPrel($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByDemande($pgCmdDemande) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.demande = :pgCmdDemande";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdDemande', $pgCmdDemande->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByStation($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.station = :pgRefStationMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByPeriode($pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevBySupport($pgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.codeSupport = :pgSandreSupports";
        $query = $query . " order by p.station,  p.periode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
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
        $query = $query . " where support.codeSupport = :pgSandreSupports ";
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
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevBySupportPresta($pgSandreSupports, $pgProgWebUser) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.codeSupport = :pgSandreSupports";
        $query = $query . "  and presta_prel_id = :pgProgWebUser";
        $query = $query . " order by p.station,  p.periode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
        $qb->setParameter('pgProgWebUser', $pgProgWebUser->getPrestataire());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.demande = :pgCmdDemande";
        $query = $query . " and p.station = :pgRefStationMesure";
        $query = $query . " and p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdDemande', $pgCmdDemande->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByPrestaPrelDemandePeriode($pgRefCorresPresta, $pgCmdDemande, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = :pgRefCorresPresta";
        $query = $query . " and p.demande = :pgCmdDemande";
        $query = $query . " and p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        $qb->setParameter('pgCmdDemande', $pgCmdDemande->getId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgRefCorresPresta, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = :pgRefCorresPresta";
        $query = $query . " and p.demande = :pgCmdDemande";
        $query = $query . " and p.station = :pgRefStationMesure";
        $query = $query . " and p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        $qb->setParameter('pgCmdDemande', $pgCmdDemande->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevUniqueByPrestaPrelDemandeStationPeriode($pgRefCorresPresta, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev p";
        $query = $query . " where p.prestaPrel = :pgRefCorresPresta";
        $query = $query . " and p.demande = :pgCmdDemande";
        $query = $query . " and p.station = :pgRefStationMesure";
        $query = $query . " and p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorid());
        $qb->setParameter('pgCmdDemande', $pgCmdDemande->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query . '<br/>');
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
        // print_r($query . '<br/>');
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

    public function getCountPgCmdPrelevByLotan($pgProgLotAn) {
        $query = "select count(prel.id)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotStationAn stan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean,";
        $query .= "          Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where ltan.id = stan.lotan";
        $query .= " and ltan.id = pean.lotan";
        $query .= " and pean.codeStatut !='INV'";
        $query .= " and lot.id = ltan.lot";
        $query .= " and ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        // $query .= " and dmd.typeDemande != '2'";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = prel.prestaPrel";
        $query .= " and typFic.formatFic like '%Saisie%'";
        $query .= " and  prel.station = stan.station";
        $query .= " and prel.periode = pean.periode";
        // ne pa prendre les preleements en phases M60;
        $query .= " and prel.phaseDmd != 360";
        $query .= " and ltan.id = :lotan";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getid());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getCountPgCmdPrelevByLotanBis($pgProgLotAn) {
        $query = "select count(prel.id)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan";
        $query .= " where ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        // ne pa prendre les preleements en phases M60;
        $query .= " and prel.phaseDmd != 360";
        $query .= " and ltan.id = :lotan";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getid());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getCountPgCmdPrelevByLotanPhase($pgProgLotAn, $pgProgPhase) {
        $query = "select count(prel.id)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotStationAn stan,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean,";
        $query .= "          Aeag\SqeBundle\Entity\PgProgPrestaTypfic typFic";
        $query .= " where ltan.id = stan.lotan";
        $query .= " and ltan.id = pean.lotan";
        $query .= " and pean.codeStatut !='INV'";
        $query .= " and lot.id = ltan.lot";
        $query .= " and ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        // $query .= " and dmd.typeDemande != '2'";
        $query .= " and typFic.codeMilieu = lot.codeMilieu";
        $query .= " and typFic.prestataire = prel.prestaPrel";
        $query .= " and typFic.formatFic like '%Saisie%'";
        $query .= " and  prel.station = stan.station";
        $query .= " and prel.periode = pean.periode";
        $query .= " and ltan.id = :lotan";
        $query .= " and prel.phaseDmd = :phase";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getId());
        $qb->setParameter('phase', $pgProgPhase->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getCountPgCmdPrelevByLotanPhaseBis($pgProgLotAn, $pgProgPhase) {
        $query = "select count(prel.id)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
        $query .= "         Aeag\SqeBundle\Entity\PgCmdDemande dmd,";
        $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn ltan";
        $query .= " where ltan.id = dmd.lotan";
        $query .= " and dmd.id = prel.demande";
        $query .= " and ltan.id = :lotan";
        $query .= " and prel.phaseDmd = :phase";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lotan', $pgProgLotAn->getid());
        $qb->setParameter('phase', $pgProgPhase->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getDonneesBrutes($pgCmdFichierRps) {
        return array_merge($this->getDonneesBrutesAnalyse($pgCmdFichierRps), $this->getDonneesBrutesMesureEnv($pgCmdFichierRps));
    }

    public function getDonneesBrutesExport($zgeorefs, $codemilieu, $datedeb, $datefin) {
        return array_merge($this->getDonneesBrutesAnalyseExport($zgeorefs, $codemilieu, $datedeb, $datefin), $this->getDonneesBrutesMesureEnvExport($zgeorefs, $codemilieu, $datedeb, $datefin));
    }

    public function getDonneesBrutesAnalyse($pgCmdFichierRps) {

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

    public function getDonneesBrutesAnalyseExport($zgeorefs, $codemilieu, $datedeb, $datefin) {
        $zgeorefs = implode(',', $zgeorefs);
        $query = 'select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau",
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
                    join pg_prog_zgeoref_station zgsta on zgsta.station_id = prlv.station_id
                    where zgsta.zgeo_ref_id IN (' . $zgeorefs . ')
                    and lot.code_milieu = :codemilieu
                    and (prlv.date_prelev >= :datedeb
                    and prlv.date_prelev <= :datefin)';

        //limit 50000';
        //where lot.zgeo_ref_id IN (' . $zgeorefs . ') --uniquement les données des lots associés à $zgeorefs

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('codemilieu', $codemilieu);
        $stmt->bindValue('datedeb', $datedeb);
        $stmt->bindValue('datefin', $datefin);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDonneesBrutesMesureEnv($pgCmdFichierRps) {
        $query = '(select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau",
                    prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur",
                    prlv.date_prelev as "Date-heure du prélèvement", mesenv.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
                    param.nom_parametre as "Nom paramètre", \'\' as "Zone verticale", \'\' as "Profondeur",
                    \'\' as "Code Support", \'\' as "Nom Support", \'\' as "Code Fraction", \'\' as "Nom Fraction", mesenv.code_methode as "Code Méthode", meth.nom_methode as "Nom Méthode",
                    mesenv.code_remarque as "Code Remarque", mesenv.resultat as "Resultat", vpos.libelle as "Valeur textuelle", mesenv.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
                    unit.symbole as "Symbole Unité", 0 as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau",
                    resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", mesenv.commentaire as "Commentaire"
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
                    left join pg_sandre_vals_possibles_params_env vpos on vpos.code_parametre = mesenv.code_parametre and vpos.valeur = mesenv.resultat
                    where prlv.fichier_rps_id = :fichier)';

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('fichier', $pgCmdFichierRps->getId());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDonneesBrutesMesureEnvExport($zgeorefs, $codemilieu, $datedeb, $datefin) {
        $zgeorefs = implode(',', $zgeorefs);
        $query = 'select dmd.annee_prog as "Année", msr.code as "Code Station", msr.libelle as "Nom Station", msr.code_masdo as "Code masse d\'eau",
                    prlv.code_prelev_cmd as "Code du prelevement", presta.code_siret as "Siret Préleveur", presta.nom_corres as "Nom Préleveur",
                    prlv.date_prelev as "Date-heure du prélèvement", mesenv.code_parametre as "Code du paramètre", param.libelle_court as "Libellé court paramètre",
                    param.nom_parametre as "Nom paramètre", \'\' as "Zone verticale", \'\' as "Profondeur",
                    \'\' as "Code Support", \'\' as "Nom Support", \'\' as "Code Fraction", \'\' as "Nom Fraction", mesenv.code_methode as "Code Méthode", meth.nom_methode as "Nom Méthode",
                    mesenv.code_remarque as "Code Remarque", mesenv.resultat as "Resultat", vpos.libelle as "Valeur textuelle", mesenv.code_unite as "Code Unite", unit.nom_unite as "Libellé Unite",
                    unit.symbole as "Symbole Unité", 0 as "LQ", presta2.code_siret as "Siret Labo", presta2.nom_corres as "Nom Labo", resmes.code_aeag_rsx as "Code Réseau",
                    resmes.nom_rsx as "Nom Réseau", prod.code_siret as "Siret Prod", prod.nom_corres as "Nom Prod", mesenv.commentaire as "Commentaire"
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
                    left join pg_sandre_vals_possibles_params_env vpos on vpos.code_parametre = mesenv.code_parametre and vpos.valeur = mesenv.resultat
                    join pg_prog_zgeoref_station zgsta on zgsta.station_id = prlv.station_id
                    where zgsta.zgeo_ref_id IN (' . $zgeorefs . ')
                    and ((lot.code_milieu = \'LPC\' and prlvpc.zone_verticale = \'1\') or (lot.code_milieu <> \'LPC\'))
                    and lot.code_milieu = :codemilieu
                    and (prlv.date_prelev >= :datedeb
                    and prlv.date_prelev <= :datefin)';
        //limit 50000';
        //case when prlvpc.zone_verticale = \'9\' then \'6\' else prlvpc.zone_verticale end as "Zone verticale", prlvpc.profondeur as "Profondeur",
        //where lot.zgeo_ref_id IN (' . $zgeorefs . ') --uniquement les données des lots associés à $zgeorefs

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('codemilieu', $codemilieu);
        $stmt->bindValue('datedeb', $datedeb);
        $stmt->bindValue('datefin', $datefin);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAutrePrelevs($pgCmdPrelev) {
        $query = "select distinct sp.datePrel as datePrel, sup.codeSupport as codeSupport, sup.nomSupport as support";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . " , Aeag\SqeBundle\Entity\PgCmdSuiviPrel sp ";
        $query = $query . " ,Aeag\SqeBundle\Entity\PgSandreSupports sup";
        $query = $query . " where prl.station = " . $pgCmdPrelev->getStation()->getOuvFoncId();
        $query = $query . " and prl.codeSupport in ('4','10','11','13','27','69')";
        $query = $query . " and prl.codeSupport <> '" . $pgCmdPrelev->getCodeSupport()->getCodeSupport() . "'";
        $query = $query . " and sup.codeSupport = prl.codeSupport";
        $query = $query . " and prl.id = sp.prelev";
        $query = $query . " and sp.statutPrel = 'P'";
        $query = $query . " and sp.validation <> 'R'";
        $query = $query . " and sp.id = (select max(ss.id) from Aeag\SqeBundle\Entity\PgCmdSuiviPrel ss where ss.prelev = prl.id)";
        //$query = $query . " group by sp.datePrel, sup.codeSupport, sup.nomSupport";
        $query = $query . " order by sp.datePrel desc";
        $qb = $this->_em->createQuery($query);

//        if ($pgCmdPrelev->getStation()->getOuvFoncId() == 557655){
//        print_r($query);
//        }
        return $qb->getResult();
    }

    public function getPrestataireBySupport($pgSandreSupports) {
        $query = "select distinct presta";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prelev,";
        $query = $query . "         Aeag\SqeBundle\Entity\PgRefCorresPresta presta ";
        $query = $query . " where prelev.codeSupport = :pgSandreSupports";
        $query = $query . "  and prelev.prestaPrel = presta.adrCorId";
        $query = $query . " order by presta.nomCorres";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdPrelevBySupportPrestaAvisStatutValidation($pgSandreSupports, $presta, $avis, $statut, $validation) {
        $query = " SELECT prelev.id as prelevId, support.nomSupport, station.ouvFoncId, suiviprel.id as suiviPrelId ";
        $query = $query . "FROM Aeag\SqeBundle\Entity\PgCmdPrelev prelev,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgCmdSuiviPrel suiviprel,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgCmdDemande demande,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgRefStationMesure station,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgProgLotAn lotan,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgProgLot lot,";
        $query = $query . "           Aeag\SqeBundle\Entity\PgProgTypeMilieu typemilieu, ";
        $query = $query . "           Aeag\SqeBundle\Entity\PgSandreSupports support ";
        $query = $query . " where support.codeSupport = :pgSandreSupports";
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
        if ($presta) {
            $query = $query . " and prelev.prestaPrel = :presta";
        }
        if ($avis) {
            $query = $query . " and suiviprel.avis = :avis";
        }
        if ($statut) {
            $query = $query . " and suiviprel.statutPrel = :statu";
        }
        if ($validation) {
            $query = $query . " and suiviprel.validation = :validation";
        }
        $query = $query . " order by prelev.station, prelev.periode ";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
        $qb->setParameter('presta', $presta);
        $qb->setParameter('avis', $avis);
        $qb->setParameter('statut', $statut);
        $qb->setParameter('validation', $validation);
        //print_r($query);
        return $qb->getResult();
    }

}

<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgMarcheRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgMarches() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.nomMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgMarcheByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByNomMarche($nomMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarches p";
        $query = $query . " where p.nomMarche = '" . $nomMarche . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByTypeMarche($typeMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.typeMarche = '" . $typeMarche . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getPgProgMarchesType() {
        $query = "select distinct p.typeMarche";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.typeMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getAvancementGlobal() {
        
        $query = "select b.typeMarche,";
        $query = $query . " a.nb_prel, b.nb_prel_prog,";
        $query = $query . " case when a.statut_prel is null then 'Aucun suivi'";
        $query = $query . " when a.statut_prel ='F' then 'Effectué sans FT'";
        $query = $query . " when a.statut_prel ='D' then 'Effectué avec FT'";
        $query = $query . " when a.statut_prel ='N' then 'Non effectué'";
        $query = $query . " when a.statut_prel ='P' then 'Prévisionnel'";
        $query = $query . " when a.statut_prel ='R' then 'Reporté'";
        $query = $query . " end  statutPrel"; 
        $query = $query . " from ";
        $query = $query . " (select typeMarche,  count(prl.id)  nb_prel, sui.statutPrel";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . " left join (select id, prelev,"; 
        $query = $query . " case when statutPrel = 'F' and fichierRps_id is not null then 'D'";
        $query = $query . " else statutPrel end  statutPrel from Aeag\SqeBundle\Entity\PgCmdSuiviPrel) sui with prl.id = sui.prelev";
        $query = $query . " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = prl.demande";
        $query = $query . "  join Aeag\SqeBundle\Entity\PgProgLotAn lan with lan.id = dmd.lotan";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lan.lot";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgMarche ma with ma.id = lot.marche";
        $query = $query . " join Aeag\SqeBundle\Entity\PgRefCorresPresta presta with presta.adrCorId = dmd.prestataire";
        $query = $query . "  join Aeag\SqeBundle\Entity\PgRefStation_mesure sta with sta.ouvFoncId = prl.station";
        $query = $query . " where (lot.codeMilieu in ('RHB','RHM')) ";
        $query = $query . " and ((sui.id is null) or (";
        $query = $query . " sui.id = (select max(ss.id) from Aeag\SqeBundle\Entity\PgCmdSuiviPrel ss where ss.prelev = prl.id)))";
        $query = $query . " group by typeMarche,sui.statutPrel";
        $query = $query . " ) a";
        $query = $query . " join";
        $query = $query . " (select typeMarche, count(prl.id)  nb_prel_prog";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = prl.demande";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLotAn lan with lan.id = dmd.lotan";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lan.lot";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgMarche ma with ma.id = lot.marche";
        $query = $query . " where (lot.codeMilieu in ('RHB','RHM'))";
        $query = $query . " group by typeMarche";
        $query = $query . " ) b with a.typeMarche = b.typeMarche";
        $query = $query . " order by b.typeMarche, a.statutPrel";
        
        $qb = $this->_em->createQuery($query);
        print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getAvancementGlobal1() {
        
         $query =  " select ma.typeMarche,  nullif(count(prl.id),0)  nb_prel,";
         $query = $query . " case when sui.statutPrel = 'F' and coalesce(max(sui.fichierRps), 0)  <> 0 then 'D'  else sui.statutPrel end statutPrel";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . "  join Aeag\SqeBundle\Entity\PgCmdSuiviPrel  sui with prl.id = sui.prelev"; 
         $query = $query . " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = prl.demande";
        $query = $query . "  join Aeag\SqeBundle\Entity\PgProgLotAn lan with lan.id = dmd.lotan";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lan.lot";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgMarche ma with ma.id = lot.marche";
        $query = $query . " join Aeag\SqeBundle\Entity\PgRefCorresPresta presta with presta.adrCorId = dmd.prestataire";
        $query = $query . "  join Aeag\SqeBundle\Entity\PgRefStationMesure sta with sta.ouvFoncId = prl.station";
        $query = $query . " where (lot.codeMilieu in ('RHB','RHM')) ";
        $query = $query . " and ((sui.id is null) or (";
        $query = $query . " sui.id = (select max(ss.id) from Aeag\SqeBundle\Entity\PgCmdSuiviPrel ss where ss.prelev = prl.id)";
        $query = $query . " )";
        $query = $query . " )"; 
        $query = $query . " group by ma.typeMarche,sui.statutPrel";
        $query = $query . " order by ma.typeMarche, sui.statutPrel";
        
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getAvancementGlobal2() {
        
         $query =  " select ma.typeMarche,  nullif(count(prl.id),0)  nb_prel";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdPrelev prl";
        $query = $query . " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = prl.demande";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLotAn lan with lan.id = dmd.lotan";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lan.lot";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgMarche ma with ma.id = lot.marche";
        $query = $query . " where (lot.codeMilieu in ('RHB','RHM'))";
        $query = $query . " group by ma.typeMarche";
        $query = $query . " order by ma.typeMarche";
        
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }


}

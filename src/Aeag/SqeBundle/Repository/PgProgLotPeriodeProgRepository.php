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
class PgProgLotPeriodeProgRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProg() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotPeriodeProgById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeProgByStationAn($PgProgLotStationAn) {
        $query = "select prog";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg prog";
        $query = $query . "        ,Aeag\SqeBundle\Entity\PgProgLotPeriodeAn peran";
        $query = $query . "        ,Aeag\SqeBundle\Entity\PgProgPeriodes per";
        $query = $query . " where prog.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and  peran.id = prog.periodan";
        $query = $query . " and  per.id = peran.periode";
        $query = $query . " order by per.numPeriode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgByGrparAn($PgProglotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
    public function getPgProgLotPeriodeProgByPeriodeAn($PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

   public function getPgProgLotPeriodeProgByStationAnGrparAn($PgProgLotStationAn,$PgProglotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.grparAn = " . $PgProglotGrparAn->getId();
         $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeProgByStationAnPeriodeAn($PgProgLotStationAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
       // print_r($query);
       return $qb->getResult();
    }
    
     public function getPgProgLotPeriodeProgByStationAnPeriodeAnOrderByGrparAn($PgProgLotStationAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
         $query = $query . " order by  p.grparAn" ;
        $qb = $this->_em->createQuery($query);
       // print_r($query);
       return $qb->getResult();
    }
    
    
    public function getPgProgLotPeriodeProgByStationAnGrparAnPeriodeAn($PgProgLotStationAn,$PgProglotGrparAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and p.grparAn = " . $PgProglotGrparAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     public function getPgProgLotPeriodeProgByGrparAnPeriodeAn($PgProglotGrparAn,$PgProglotPeriodeAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $query = $query . " and p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function countPgProgLotPeriodeProgByGrparAn($PgProglotGrparAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function countPgProgLotPeriodeProgByStationAn($PgProgLotStationAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function countPgProgLotPeriodeProgByPeriodeAn($PgProglotPeriodeAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.periodan = " . $PgProglotPeriodeAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function getPgProgLotPeriodeProgByPeriodeAnOrderByStation($PgProglotPeriodeAn) {
        $query = "select p";
        $query .=  " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query .= " , Aeag\SqeBundle\Entity\PgProgLotStationAn sa";
        $query .= " , Aeag\SqeBundle\Entity\PgRefStationMesure s";
        $query .=  " where p.periodan = " . $PgProglotPeriodeAn->getId();
        $query .=  " and p.stationAn =  sa.id";
        $query .=  " and sa.station =  s.ouvFoncId";
        $query .=  " order by s.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function countStationAnByGrparAn($PgProglotGrparAn) {
        $query = "select count( distinct p.stationAn)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.grparAn = " . $PgProglotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function getPgProgLotPeriodeProgByPprogCompl($PgProgLotPeriodeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.pprogCompl = " . $PgProgLotPeriodeProg->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotPeriodeProgAutres($pgProgLotGrparAn, $pgProgPeriode, $pgProgLotStationAn, $pgProgLotAn, $phaseIdMin) {
        
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query .= " join p.periodan pean";
        $query .= " join p.grparAn gran";
        $query .= " join p.stationAn stan";
        $query .= " join stan.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " where pean.periode = ".$pgProgPeriode->getId()." AND pean.codeStatut <> 'INV' ";
        $query .= " and gran.grparRef = ".$pgProgLotGrparAn->getGrparRef()->getId();
        $query .= " and stan.station = ".$pgProgLotStationAn->getStation()->getOuvFoncId();
        $query .= " and lotan.lot <> ".$pgProgLotAn->getLot()->getId()." and lotan.phase > ".$phaseIdMin; // phase >= P25
        $query .= " and lot.codeMilieu = '".$pgProgLotAn->getLot()->getCodeMilieu()->getCodeMilieu()."'";
        $query .= " and lotan.anneeProg = ".$pgProgLotAn->getAnneeProg();
        
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
        
    }
    
    public function getPgProgLotPeriodeProgAutresGroupesRef($pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns, $phaseIdMin) {
        
        $listGrparRef = Array();
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $listGrparRef[] = $pgProgLotGrparAn->getGrparRef();
        }
        
        $query = "select distinct grparRefan.id";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query .= " join p.periodan pean";
        $query .= " join p.grparAn gran";
        $query .= " join p.stationAn stan";
        $query .= " join stan.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " join gran.grparRef grparRefan";
        $query .= " where pean.periode = :periodeId AND pean.codeStatut <> 'INV' ";
        $query .= " and stan.station = :stationId";
        $query .= " and lotan.phase > :phaseId"; // phase >= P25
        $query .= " and lotan.anneeProg = :anneeProg";
        $query .= " and lot.id <> :lotId";
        $query .= " and lot.codeMilieu = :codeMilieu";
        $query .= " and gran.id IN (:grparRefs)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('periodeId', $pgProgPeriode->getId());
        $qb->setParameter('stationId', $pgProgLotStationAn->getStation()->getOuvFoncId());
        $qb->setParameter('phaseId', $phaseIdMin);
        $qb->setParameter('anneeProg', $pgProgLotStationAn->getLotan()->getAnneeProg());
        $qb->setParameter('lotId', $pgProgLotStationAn->getLotan()->getLot()->getId());
        $qb->setParameter('codeMilieu', $pgProgLotStationAn->getLotan()->getLot()->getCodeMilieu()->getCodeMilieu());
        $qb->setParameter('grparRefs', $listGrparRef);
        
        return $qb->getResult();
    }
    
     public function getPgProgLotPeriodeProgByPgProgLotAn($pgProgLotAn) {

        $query = "select lotan.id as lotan_id, lotan.lot_id, lotan.annee_prog, lotan.phase_id, lotan.date_modif, lotan.util_modif, lotan.code_statut, lotan.lotan_pere_id, lotan.version,";
        $query = $query . " stan.id as stan_id, stan.station_id, stan.rsx_id as sta_rsx_id,";
        $query = $query . " pean.id as pean_id, pean.periode_id, pean.code_statut,";
        $query = $query . "  gran.id as gran_id, gran.grpar_ref_id, gref.type_grp, gran.presta_dft_id, presta.nom_corres, gran.valide,";
        $query = $query . " pprog.id as pprog_id, pprog.pprog_compl_id, pprog.statut, pprog.rsx_id as pprog_rsx_id,";
        $query = $query . " lotana.id as autre_lotan_id, lotana.lot_id as autre_lot_id, lota.nom_lot as autre_lot_nom, grana.presta_dft_id as autre_presta_id, prestaa.nom_corres as autre_presta_nom";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn lotan";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLot lot on lot.id = lotan.lot_id";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLotStationAn stan on stan.lotan_id = lotan.id";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean on pean.lotan_id = lotan.id";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgLotGrparAn gran on gran.lotan_id = lotan.id and gran.valide = 'O'";
        $query = $query . " join Aeag\SqeBundle\Entity\PgProgGrpParam_ref gref on gref.id = gran.grpar_ref_id";
        $query = $query . " join Aeag\SqeBundle\Entity\PgRefCorresPresta presta on presta.adr_cor_id = gran.presta_dft_id";
        $query = $query . " join pg_prog_lot_periode_prog pprog on pprog.grpar_an_id = gran.id and pprog.station_an_id = stan.id and pprog.periodan_id = pean.id";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLotPeriodeProg ppa on ppa.id = pprog.pprog_compl_id";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLotStationAn stana on stana.id = ppa.station_an_id and stana.station_id = stan.station_id";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLotPeriodeAn peana on peana.id = ppa.periodan_id and peana.periode_id = pean.periode_id and peana.code_statut <> 'INV'";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLotGrparAn grana on grana.id = ppa.grpar_an_id and grana.valide = 'O' and grana.grpar_ref_id = gran.grpar_ref_id";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgPefCorresPresta prestaa on prestaa.adr_cor_id = grana.presta_dft_id";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLotAn lotana on lotana.id = stana.lotan_id and lotana.lot_id <> lotan.lot_id and lotana.phase_id > 3 and lotana.phase_id < 9";
        $query = $query . " left join Aeag\SqeBundle\Entity\PgProgLot lota on lota.id = lotana.lot_id and lota.code_milieu = lot.code_milieu";
        $query = $query . " where lotan.id = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

  
}

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
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeProg p";
        $query = $query . " where p.stationAn = " . $PgProgLotStationAn->getId();
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
    
  
}

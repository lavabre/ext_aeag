<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdSuiviPrelRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdSuiviPrelRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdSuiviPrels() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelById($id) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevOrderDate($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query .  " order by c.datePrel desc, c.id desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query .  " order by c.id desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevMaxId($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
         $query = $query . " where c.id in (select max(s.id) from Aeag\SqeBundle\Entity\PgCmdSuiviPrel s";
        $query = $query . " where s.prelev = " . $pgCmdPrelev->getId()  . ")";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, $statutPrel) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query . " and c.statutPrel = '" . $statutPrel . "'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getEvenements($date, $support, $station, $presta, $typemilieu) {
        
        $query = "select c";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query .= " join c.prelev p";
        $query .= " join p.demande dmd ";
        $query .= " join dmd.lotan lotan ";
        $query .= " join lotan.lot lot ";
        $query .= " join lot.codeMilieu mil ";
        $query .= " where c.statutPrel = 'P'";
        $query .= " and (c.datePrel >= :dateDebut and c.datePrel <= :dateFin)";
        if($support != "") {
            $query .= " and p.codeSupport = :support";
        }
        if($station != "") {
            $query .= " and p.station = :station";
        }
        
        if($presta != "") {
            $query .= " and p.prestaPrel = :presta";
        }
        
        $query .= " and mil.codeMilieu LIKE :typemilieu";
     
        $query .= " and c IN (";
        $query .= " select max(c1)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c1";
        $query .= " group by c1.prelev";
        $query .= " order by c1.prelev)";
        $query .= " order by c.datePrel asc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dateDebut', $date);
        $dateFin = clone $date;
        $dateFin->add(new \DateInterval('PT23H59M59S'));
        $qb->setParameter('dateFin', $dateFin);
        if($support != "") {
            $qb->setParameter('support', $support);
        }
        if($station != "") {
            $qb->setParameter('station', $station);
        }
        if($presta != "") {
            $qb->setParameter('presta', $presta);
        }
        if (strlen($typemilieu) == 3) {
            $qb->setParameter('typemilieu', $typemilieu);
        } else { // == 2
            $qb->setParameter('typemilieu', '%'.$typemilieu);
        }
        //$qb->setMaxResults(1);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getSupportsFromSuiviPrelByCodeMilieu($codeMilieu) {
        $query = "select distinct s";
        $query .= " from Aeag\SqeBundle\Entity\PgSandreSupports s";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with s.codeSupport = p.codeSupport ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = p.demande ";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLotAn lotan with lotan.id = dmd.lotan";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lotan.lot";
        $query .= " join Aeag\SqeBundle\Entity\PgProgTypeMilieu mil with mil.codeMilieu = lot.codeMilieu";
        $query .= " where mil.codeMilieu like :codeMilieu";
        $query .= " order by s.codeSupport asc";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeMilieu', '%'.$codeMilieu);
        return $qb->getResult();
        
    }
    
    public function getStationsFromSuiviPrelByCodeMilieu($codeMilieu) {
        $query = "select distinct s";
        $query .= " from Aeag\SqeBundle\Entity\PgRefStationMesure s";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with s.ouvFoncId = p.station ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = p.demande ";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLotAn lotan with lotan.id = dmd.lotan";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lotan.lot";
        $query .= " join Aeag\SqeBundle\Entity\PgProgTypeMilieu mil with mil.codeMilieu = lot.codeMilieu";
        $query .= " where mil.codeMilieu like :codeMilieu";
        $query .= " order by s.libelle asc";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeMilieu', '%'.$codeMilieu);
        return $qb->getResult();
        
    }
    
    public function getPrestatairesFromSuiviPrelByCodeMilieu($codeMilieu) {
        $query = "select distinct cp";
        $query .= " from Aeag\SqeBundle\Entity\PgRefCorresPresta cp";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with cp.adrCorId = p.prestaPrel ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.id = p.demande ";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLotAn lotan with lotan.id = dmd.lotan";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.id = lotan.lot";
        $query .= " join Aeag\SqeBundle\Entity\PgProgTypeMilieu mil with mil.codeMilieu = lot.codeMilieu";
        $query .= " where mil.codeMilieu like :codeMilieu";
        $query .= " order by cp.nomCorres asc";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeMilieu', '%'.$codeMilieu);
        return $qb->getResult();
        
    }
    
    public function getTypesMilieuFromSuiviPrelByCodeMilieu($codeMilieu) {
        
        $query = "select distinct mil";
        $query .= " from Aeag\SqeBundle\Entity\PgProgTypeMilieu mil";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLot lot with lot.codeMilieu = mil.codeMilieu";
        $query .= " join Aeag\SqeBundle\Entity\PgProgLotAn lotan with lotan.lot = lot.id";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdDemande dmd with dmd.lotan = lotan.id ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev prel with prel.demande = dmd.id ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel suivi with suivi.prelev = prel.id";
        $query .= " where mil.codeMilieu like :codeMilieu";
        $query .= " order by mil.nomMilieu asc";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeMilieu', '%'.$codeMilieu);
        return $qb->getResult();
        
    }
    
    
    public function getSuiviPrelP15j() {
        /*
         *select suivi2.* from pg_cmd_suivi_prel suivi2 where suivi2.id IN (
            select max(suivi.id)
            from pg_cmd_suivi_prel suivi
            group by suivi.prelev_id
            order by suivi.prelev_id)
            and statut_prel = 'P'
            and date_prel < now() 
         */
        $query = "select suivi2";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel suivi2";
        $query .= " where suivi2 IN (";
        $query .= " select max(suivi)";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel suivi";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with suivi.prelev = p.id ";
        $query .= " group by p.id)";
        $query .= " and suivi2.statutPrel = 'P'";
        $query .= " and DATE_ADD(suivi2.datePrel, 15, 'day') < CURRENT_TIMESTAMP()";
        
        $qb = $this->_em->createQuery($query);
        
        return $qb->getResult();
        
    }
    
}

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
        $query = $query .  " order by c.id desc, c.datePrel desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
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
    
    public function getEvenements($date, $support, $station, $presta) {
        $query = "select c";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query .= " join c.prelev p";
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
        //$qb->setMaxResults(1);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getSupportsFromSuiviPrel() {
        $query = "select distinct s";
        $query .= " from Aeag\SqeBundle\Entity\PgSandreSupports s";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with s.codeSupport = p.codeSupport ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " order by s.codeSupport asc";
        
        $qb = $this->_em->createQuery($query);
        
        return $qb->getResult();
        
    }
    
    public function getStationsFromSuiviPrel() {
        $query = "select distinct s";
        $query .= " from Aeag\SqeBundle\Entity\PgRefStationMesure s";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with s.ouvFoncId = p.station ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " order by s.libelle asc";
        
        $qb = $this->_em->createQuery($query);
        
        return $qb->getResult();
        
    }
    
    public function getPrestatairesFromSuiviPrel() {
        $query = "select distinct cp";
        $query .= " from Aeag\SqeBundle\Entity\PgRefCorresPresta cp";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdPrelev p with cp.adrCorId = p.prestaPrel ";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdSuiviPrel c with c.prelev = p.id ";
        $query .= " order by cp.nomCorres asc";
        
        $qb = $this->_em->createQuery($query);
        
        return $qb->getResult();
        
    }
    
    
}

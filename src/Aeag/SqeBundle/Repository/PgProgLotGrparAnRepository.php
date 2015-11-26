<?php

/**
 * Description of PgProgLotGrparAnRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotGrparAnRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotGrparAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotGrparAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " order by p.lot";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotGrparAnByLotan($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnByGrpparref($PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
   
    public function getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $query = $query . " and p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getPgProgLotGrparAnByPrestataire($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $query = $query . " order by p.prestaDft";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotGrparAnByLotAnPrestaDft($pgProgLotAn, $prestaDft) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $query = $query . " and p.prestaDft = " . $prestaDft->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgProgLotGrparAnByLotAnPrestaDftGrpparref($pgProgLotAn, $prestaDft, $PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $pgProgLotAn->getId();
        $query = $query . " and p.prestaDft = " . $prestaDft->getAdrCorId();
        $query = $query . " and p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function countPgProgLotGrparAnByLotan($PgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function countPgProgLotGrparAnByValide($PgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . "and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function countPgProgLotGrparAnByNonValide($PgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.valide = 'N'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

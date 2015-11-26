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
class PgProgLotParamAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotParamAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotParamAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamAnByRsxId($rsxId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.rsx = " . $rsxId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByPrestataire($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByGrparan($PgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotParamAnByGrparAnCodeParametre($PgProgLotGrparAn, $PgSandreParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $query = $query . " and p.codeParametre = '" . $PgSandreParametre->getCodeParametre() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgProgLotParamAnByPrestataireGrparan($PgRefCorresPresta, $PgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = " . $PgRefCorresPresta->getAdrCorId();
        $query = $query . " and p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    
     public function getPrestatairesByGrparan($PgProgLotGrparAn) {
        $query = "select distinct(p.prestataire)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
}

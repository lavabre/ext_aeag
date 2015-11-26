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
class PgProgLotParamPprogRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotParamPprog() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotParamPprogById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamPprogByRsxId($rsxId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.rsx = " . $rsxId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotParamPprogByPeriodeProg($PgProgLotPeriodeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = " . $PgProgLotPeriodeProg->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }


    public function getPgProgLotParamPprogByPrestataire($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.prestataire = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotParamPprogByLot($PgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.lot = " . $PgProgLot->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotParamPprogByPeriodeProgPgGrparRefLstParam($PgProgLotPeriodeProg, $PgGrparRefLstParam) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = " . $PgProgLotPeriodeProg->getid();
        $query = $query . " and p.codeParametre = '" . $PgGrparRefLstParam->getCodeParametre()->getCodeParametre() ."'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgProgLotParamPprogByPeriodeProgPrestataire($PgProgLotPeriodeProg, $prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = " . $PgProgLotPeriodeProg->getid();
        $query = $query . " where p.prestataire = " . $prestataire;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

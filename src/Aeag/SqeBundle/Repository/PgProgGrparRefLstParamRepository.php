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
class PgProgGrparRefLstParamRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgGrparRefLstParams() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByCodeParametre($codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.codeParametre = '" . $codeParametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByGrparRef($PgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getNbPgProgGrparRefLstParamByGrparRef($PgProgGrpParamRef) {
        $query = "select count(p.codeParametre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $qb = $this->_em->createQuery($query);
       // print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    public function getNbPgProgGrparRefLstParamValideByGrparRef($PgProgGrpParamRef) {
       $query = "select count(p.codeParametre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $query = $query . " and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
      public function getPgProgGrparRefLstParamValideByGrparRef($PgProgGrpParamRef) {
       $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $query = $query . " and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByCodeFraction($PgSandreFractions) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.codeFraction = '" . $PgSandreFractions->getCodeFraction() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgGrparRefLstParamByGrparRefCodeParametre($PgProgGrpParamRef,$codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = " . $PgProgGrpParamRef->getid();
        $query = $query . " and p.codeParametre = '" . $codeParametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

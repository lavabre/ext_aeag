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
        $query = $query . " where p.codeParametre = :codeParametre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeParametre', $codeParametre);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef) {
        $query = "select count(p.codeParametre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbPgProgGrparRefLstParamValideByGrparRef($pgProgGrpParamRef) {
        $query = "select count(p.codeParametre)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $query = $query . " and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgProgGrparRefLstParamValideByGrparRef($pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $query = $query . " and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByCodeFraction($pgSandreFractions) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.codeFraction = :pgSandreFractions";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreFractions', $pgSandreFractions->getCodeFraction());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefLstParamByGrparRefCodeParametre($pgProgGrpParamRef, $codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefLstParam p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $query = $query . " and p.codeParametre = :codeParametre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        $qb->setParameter('codeParametre', $codeParametre);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

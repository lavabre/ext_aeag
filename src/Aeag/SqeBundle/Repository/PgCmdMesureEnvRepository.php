<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdMesureEnvRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdMesureEnvRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdMesureEnvs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdMesureEnvByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbCmdMesureEnvByPrelev($pgCmdPrelev) {
        $query = "select count(c.prelev)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, $statut) {
        $query = "select count(c.prelev)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $query = $query . " and  c.codeStatut = '" . $statut . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $query = $query . " and  c.paramProg = " . $pgProgLotParamAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgCmdMesureEnvsByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $query = $query . " and  c.paramProg = " . $pgProgLotParamAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $parametre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $query = $query . " and  c.codeParametre = '" . $parametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgCmdMesureEnvByPrelevCodeUniteParametre($pgCmdPrelev, $codeUnite, $parametre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMesureEnv c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId();
        $query = $query . " and  c.codeUnite = '" . $codeUnite . "'";
        $query = $query . " and  c.codeParametre <> '" . $parametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return  $qb->getResult();
    }

}

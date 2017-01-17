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
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamPprogByRsxId($rsxId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.rsx =  :rsxId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('rsxId', $rsxId);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamPprogByPeriodeProg($pgProgLotPeriodeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = :pgProgLotPeriodeProg";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotPeriodeProg', $pgProgLotPeriodeProg->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamPprogByPrestataire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.prestataire = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamPprogByLot($pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.lot = :pgProgLot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLot', $pgProgLot->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamPprogByPeriodeProgPgGrparRefLstParam($pgProgLotPeriodeProg, $pgGrparRefLstParam) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = :pgProgLotPeriodeProg";
        $query = $query . " and p.codeParametre = :pgGrparRefLstParam";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotPeriodeProg', $pgProgLotPeriodeProg->getid());
        $qb->setParameter('pgGrparRefLstParam', $pgGrparRefLstParam->getCodeParametre()->getCodeParametre());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamPprogByPeriodeProgPrestataire($pgProgLotPeriodeProg, $prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamPprog p";
        $query = $query . " where p.pprog = :pgProgLotPeriodeProg";
        $query = $query . " where p.prestataire = :prestataire";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotPeriodeProg', $pgProgLotPeriodeProg->getid());
        $qb->setParameter('prestataire', $prestataire);
        //print_r($query);
        return $qb->getResult();
    }

}

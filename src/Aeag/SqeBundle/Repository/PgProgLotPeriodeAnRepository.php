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
class PgProgLotPeriodeAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPeriodeAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotPeriodeAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeAnByLotan($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " order by p.periode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeAnByPeriode($pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.periode = :pgProgPeriode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.periode = :pgProgPeriodes";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgProgPeriodes', $pgProgPeriodes->getId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function countPgProgLotPeriodeAnByLotan($pgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

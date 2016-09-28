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
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPeriodeAnByLotan($PgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " order by p.periode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeAnByPeriode($PgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.periode = " . $PgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPeriodeAnBySLotanPeriode($PgProgLotAn, $PgProgPeriodes) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.periode = " . $PgProgPeriodes->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function countPgProgLotPeriodeAnByLotan($PgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPeriodeAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

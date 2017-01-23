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
class PgProgPeriodesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgPeriodes() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPeriodes p";
        $query = $query . " order by p.anneeProg, p.numPeriode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgPeriodesById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPeriodes p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgPeriodesByNumPeriode($numPeriode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPeriodes p";
        $query = $query . " where p.numPeriode = :numPeriode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('numPeriode', $numPeriode);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgPeriodesByTypePeriode($pgProgTypePeriode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPeriodes p";
        $query = $query . " where p.typePeriode = :pgProgTypePeriode";
        $query = $query . " order by p.anneeProg, p.numPeriode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypePeriode', $pgProgTypePeriode->getCodeTypePeriode());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypePeriode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPeriodes p";
        $query = $query . " where p.anneeProg = :annee";
        $query = $query . " and p.typePeriode = :pgProgTypePeriode";
        $query = $query . " order by p.numPeriode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('pgProgTypePeriode', $pgProgTypePeriode->getCodeTypePeriode());
        //print_r($query);
        return $qb->getResult();
    }

}

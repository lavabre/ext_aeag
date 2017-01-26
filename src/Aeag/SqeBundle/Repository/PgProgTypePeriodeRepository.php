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
class PgProgTypePeriodeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgTypePeriode() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypePeriode p";
        $query = $query . " order by p.codeTypePeriode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgTypePeriodeById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypePeriode p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgTypePeriodeByCodeTypePeriode($codeTypePeriode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypePeriode p";
        $query = $query . " where p.codeTypePeriode = :codeTypePeriode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeTypePeriode', $codeTypePeriode);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

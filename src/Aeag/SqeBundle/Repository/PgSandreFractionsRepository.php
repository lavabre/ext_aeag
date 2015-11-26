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
class PgSandreFractionsRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreFractions() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreFractions p";
        $query = $query . " order by p.codeFraction";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgSandreFractionsByCodeFraction($codeFraction) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreFractions p";
        $query = $query . " where p.codeFraction = '" . $codeFraction . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgSandreFractionsByCodeSupport($PgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreFractions p";
        $query = $query . " where p.codeSupport = '" . $PgSandreSupports->getCodeSupport() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

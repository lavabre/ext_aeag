<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgUnitesPossiblesParamRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgUnitesPossiblesParamRepository extends EntityRepository {
    
    /**
     * @return array
     */
    public function getPgProgUnitesPossiblesParam() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgUnitesPossiblesParam p";
        $query = $query . " order by p.codeParametre,  p.codeUnite";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgUnitesPossiblesParamByCodeParametre($codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgUnitesPossiblesParam p";
        $query = $query . " where p.codeParametre = '" . $codeParametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgUnitesPossiblesParamWithValeurMax() {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgUnitesPossiblesParam p";
        $query .= " where p.valMax IS NOT NULL";
        $query .= " order by p.codeParametre,  p.codeUnite";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    
}

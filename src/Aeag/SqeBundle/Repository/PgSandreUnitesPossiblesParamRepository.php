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
class PgSandreUnitesPossiblesParamRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreUnitesPossiblesParam() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnitesPossiblesParam p";
        $query = $query . " order by p.codeParametre,  p.codeUnite";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreUnitesPossiblesParamByCodeParametre($codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnitesPossiblesParam p";
        $query = $query . " where p.codeParametre = '" . $codeParametre . "'";
         $query = $query . " order by  p.valeur";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

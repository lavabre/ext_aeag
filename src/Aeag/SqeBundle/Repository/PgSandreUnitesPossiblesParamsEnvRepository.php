<?php

/**
 * Description of PgSandreUnitesPossiblesParamsEnvRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgSandreUnitesPossiblesParamsEnvRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreUnitesPossiblesParamsEnv() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnitesPossiblesParamsEnv p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreUnitesPossiblesParamsEnvByCodeParametre($codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnitesPossiblesParamsEnv p";
        $query = $query . " where p.codeParametre = '" . $codeParametre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

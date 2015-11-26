<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgSandreSupportsRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgSandreSupportsRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreSupports() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreSupports p";
        $query = $query . " order by p.codeSupport";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgSandreSupportsByCodeSupport($codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreSupports p";
        $query = $query . " where p.codeSupport = '" . $codeSupport . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

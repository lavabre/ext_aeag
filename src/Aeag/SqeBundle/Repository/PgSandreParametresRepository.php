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
class PgSandreParametresRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreParametres() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreParametres p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreParametresByCodeParametre($codeParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreParametres p";
        $query = $query . " where p.codeParametre = :codeParametre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeParametre', $codeParametre);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

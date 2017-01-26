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
class PgSandreUnitesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreUnites() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnites p";
        $query = $query . " order by p.codeUnite";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreUnitesByCodeUnite($codeUnite) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreUnites p";
        $query = $query . " where p.codeUnite = :codeUnite";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeUnite', $codeUnite);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

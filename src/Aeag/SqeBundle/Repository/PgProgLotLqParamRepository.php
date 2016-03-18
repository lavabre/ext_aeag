<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotLqParamRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotLqParamRepository extends EntityRepository {

    public function isValidLq($lot, $codeParametre, $codeFraction, $lq) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgLotLqParam p";
        $query .= " where p.lot = :lot";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codeFraction = :codeFraction";
        $query .= " and p.lqPrestataire >= :lq";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lot', $lot);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codeFraction', $codeFraction);
        $qb->setParameter('lq', $lq);
        $results = $qb->getResults();

        return $results;
    }

}

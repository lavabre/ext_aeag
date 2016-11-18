<?php
namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgBornesParamsRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgBornesParamsRepository extends EntityRepository {
    
    public function getPgProgBornesParamsByCodeMilieu($codeMilieu) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgBornesParams p";
        $query .= " where p.valMax IS NOT NULL";
        $query .= " and p.codeMilieu = :codeMilieu";
        $query .= " order by p.codeParametre,  p.codeFraction";
        $qb = $this->_em->createQuery($query);
        
        $qb->setParameter('codeMilieu', $codeMilieu);
        //print_r($query);
        return $qb->getResult();
    }
}

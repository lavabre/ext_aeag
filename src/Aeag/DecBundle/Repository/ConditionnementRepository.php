<?php

/**
 * Description of ConditionnementRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ConditionnementRepository
 * @package Aeag\DecBundle\Repository
 */
class ConditionnementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getConditionnements() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Conditionnement c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getConditionnementByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Conditionnement c";
        $query = $query . " where c.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

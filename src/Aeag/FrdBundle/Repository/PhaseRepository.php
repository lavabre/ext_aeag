<?php

/**
 * Description of PhaseRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PhaseRepository
 * @package Aeag\FrdBundle\Repository
 */
class PhaseRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPhases() {
        $query = "select c";
        $query = $query . " from Aeag\FrdBundle\Entity\Phase c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPhaseByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\FrdBundle\Entity\Phase c";
        $query = $query . " where c.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

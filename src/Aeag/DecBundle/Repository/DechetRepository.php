<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RefDechetRepository
 * @package Aeag\DecBundle\Repository
 */
class DechetRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDechets() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Dechet c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDechetsAidables($aidable) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Dechet c";
        $query = $query . " where c.aidable = :aidable";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aidable', $aidable);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDechetByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Dechet c";
        $query = $query . " where c.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

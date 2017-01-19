<?php

/**
 * Description of NafRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class NafRepository
 * @package Aeag\DecBundle\Repository
 */
class NafRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getNafs() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Naf c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getNafsAidables($aidable) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Naf c";
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
    public function getNafByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Naf c";
        $query = $query . " where c.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

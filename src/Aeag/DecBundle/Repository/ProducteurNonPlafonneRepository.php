<?php

/**
 * Description of ProducteurNonPlafonneRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProducteurNonPlafonneRepository
 * @package Aeag\DecBundle\Repository
 */
class ProducteurNonPlafonneRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getProducteurNonPlafonnes() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\ProducteurNonPlafonne c";
        $query = $query . " order by c.siret";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getProducteurNonPlafonnesAidables($aidable) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\ProducteurNonPlafonne c";
        $query = $query . " where c.aidable = :aidable";
        $query = $query . " order by c.siret";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('aidable', $aidable);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getProducteurNonPlafonneBySiret($siret) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\ProducteurNonPlafonne c";
        $query = $query . " where c.siret = :siret";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('siret', $siret);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of ProducteurTauxSpecialRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProducteurTauxSpecialRepository
 * @package Aeag\DecBundle\Repository
 */
class ProducteurTauxSpecialRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getProducteurTauxSpecials() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\ProducteurTauxSpecial c";
        $query = $query . " order by c.siret";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
   
    /**
     * @return array
     */
    public function getProducteurTauxSpecialBySiret($siret) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\ProducteurTauxSpecial c";
        $query = $query . " where c.siret = '" . $siret . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

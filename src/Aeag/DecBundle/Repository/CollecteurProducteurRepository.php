<?php

/**
 * Description of CollecteurProducteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CollecteurProducteurRepository
 * @package Aeag\DecBundle\Repository
 */
class CollecteurProducteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCollecteurProducteurs() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\CollecteurProducteur c";
        $query = $query . " order by c.Collecteur,c.Producteur";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getCollecteurProducteurByCollecteur($collecteur) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\CollecteurProducteur c";
        $query = $query . " where c.Collecteur = " . $collecteur;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getCollecteurProducteurByProducteur($producteur) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\CollecteurProducteur c";
        $query = $query . " where c.Producteur = " . $producteur;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getNbCollecteurProducteurByProducteur($producteur) {
        $query = "select count(c.id)";
        $query = $query . " from Aeag\DecBundle\Entity\CollecteurProducteur c";
        $query = $query . " where c.Producteur = " . $producteur;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getCollecteurProducteurByCollecteurProducteur($collecteur, $producteur) {
        $query = "select distinct c";
        $query = $query . " from Aeag\DecBundle\Entity\CollecteurProducteur c";
        $query = $query . " where c.Collecteur = " . $collecteur;
        $query = $query . " and c.Producteur = " . $producteur;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

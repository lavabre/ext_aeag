<?php

/**
 * Description of DemandeRepository
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DemandeRepository
 * @package Aeag\DieBundle\Repository
 */
class DemandeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDemandes() {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Demande c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getDemandesByNom($nom) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Demande c";
        $query = $query . " where c.nom = '" . $nom . "'";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDemandeById($id) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Demande c";
        $query = $query . " where c.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of OuvrageCorrespondantRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OuvrageCorrespondantRepository
 * @package Aeag\AeagBundle\Repository
 */
class OuvrageCorrespondantRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getOuvrageCorrespondants() {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\OuvrageCorrespondant c";
        $query = $query . " order by c.Ouvrage.numero,c.Correspondant.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOuvrageCorrespondantByOuvrage($ouvrage) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\OuvrageCorrespondant c";
        $query = $query . " where c.Ouvrage = " . $ouvrage;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOuvrageCorrespondantByCorrespondant($correspondant) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\OuvrageCorrespondant c";
        $query = $query . " where c.Correspondant = '" . $correspondant . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOuvrageCorrespondantByOuvrageCorrespondant($ouvrage, $correspondant) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\OuvrageCorrespondant c";
        $query = $query . " where c.Ouvrage = '" . $ouvrage . "'";
        $query = $query . " and c.Correspondant = '" . $correspondant . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getOuvrageByCorrespondantType($correspondant,$type) {
        $query = "select o";
        $query = $query . " from Aeag\AeagBundle\Entity\OuvrageCorrespondant c,";
        $query = $query . "   Aeag\AeagBundle\Entity\Ouvrage o";
        $query = $query . " where c.Correspondant = '" . $correspondant . "'";
        $query = $query . " and o.id = c.Ouvrage";
        $query = $query . " and o.type = '" . $type . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

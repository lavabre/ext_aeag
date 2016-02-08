<?php

/**
 * Description of OuvrageRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OuvrageRepository
 * @package Aeag\AeagBundle\Repository
 */
class OuvrageRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getOuvrages() {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " order by c.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getOuvrageById($Id) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.id = " . $Id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getOuvragesByType($type) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.type = '" . $type . "'";
        $query = $query . " order by c.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }


    /**
     * @return array
     */
    public function getOuvrageByNumero($numero) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.numero = '" . $numero . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getOuvrageByNumeroType($numero,$type) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.numero = '" . $numero . "'";
        $query = $query . " and c.type = '" . $type . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getOuvrageByOuvId($ouvId) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.ouvId = " . $ouvId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getOuvrageByOuvIdType($ouvId,$type) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.ouvId = " . $ouvId;
        $query = $query . " and c.type = '" . $type . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getOuvrageBySiret($siret) {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.siret = '" . $siret . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getOuvrageBySiretType($siret,$type) {
        $query = "select distinct c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.siret = '" . $siret . "'";
        $query = $query . " and c.type = '" . $type . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
      }
    
      /**
     * @return array
     */
    public function getOuvragesBySiretType($siret,$type) {
        $query = "select distinct c";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage c";
        $query = $query . " where c.siret = '" . $siret . "'";
        $query = $query . " and c.type = '" . $type . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        //return $qb->getOneOrNullResult();
         return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getOuvrageByUserIdType($userId, $type) {
        $query = "select o";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage o";
        $query = $query . "   ,Aeag\AeagBundle\Entity\OuvrageCorrespondant oc";
        $query = $query . "   ,Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . "   ,Aeag\UserBundle\Entity\User u";
        $query = $query . " where o.id = oc.Ouvrage";
        $query = $query . " and o.type = '" . $type . "'";
        $query = $query . " and oc.Correspondant = c.id";
        $query = $query . " and c.id = u.correspondant" ;
        $query = $query . " and u.id = " . $userId ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getOuvrageByUserNameType($userName, $type) {
        $query = "select o";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage o";
        $query = $query . "   ,Aeag\AeagBundle\Entity\OuvrageCorrespondant oc";
        $query = $query . "   ,Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . "   ,Aeag\UserBundle\Entity\User u";
        $query = $query . " where o.id = oc.Ouvrage";
        $query = $query . " and o.type = '" . $type . "'";
        $query = $query . " and oc.Correspondant = c.id";
        $query = $query . " and c.id = u.correspondant" ;
        $query = $query . " and u.username = '" . $userName . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    /*public function getProducteursByCollecteur($collecteur) {
        $query = "select p";
        $query = $query . " from Aeag\AeagBundle\Entity\Ouvrage p";
        $query = $query . "     , Aeag\DecBundle\Entity\CollecteurProducteur cp";
        $query = $query . " where p.id = cp.Producteur";
        $query = $query . " and cp.Collecteur = " . $collecteur;
        $query = $query . " order by p.libelle, p.siret";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
       return $qb->getResult();
    }*/

}

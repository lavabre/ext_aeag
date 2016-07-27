<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgWebusersRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgWebusers() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " order by p.nom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebusersByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getPgProgWebusersByExtid($extId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.extId = " . $extId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgWebusersByNom($nom) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.nom = '" . $nom . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgProgWebusersByLoginPassword($login,$pwd) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.login= '" . $login . "'";
        $query = $query . " and p.pwd= '" . $pwd . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     public function getPgProgWebusersByPrestataire($prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.prestataire = " . $prestataire->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
         return $qb->getResult();
    }
    
    public function getPgProgWebusersByTypeUser($typeUser) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.typeUser = '" . $typeUser . "'";
        $query = $query . " order by p.nom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
        public function getSuppportByPrestataire($pgRefCorresPresta) {
          $query = "select distinct sup";
          $query .= " from Aeag\SqeBundle\Entity\PgProgLotParamAn pan,";
          $query .= "         Aeag\SqeBundle\Entity\\PgProgLotGrparAn gran,";
          $query .= "         Aeag\SqeBundle\Entity\PgProgGrpParamRef gref,";
          $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn lan,";
          $query .= "         Aeag\SqeBundle\Entity\PgSandreSupports sup";
          $query .= " where lan.phase > 6"; 
          $query .= " and gran.id = pan.grparan";
          $query .= " and gref.id = gran.grparRef";
          $query .= " and lan.id = gran.lotan";
           $query .= " and sup.codeSupport = gref.support";
          $query .= " and gref.support is not null";
          $query .= " and pan.prestataire = :prestataire";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('prestataire', $pgRefCorresPresta->getAdrCorId()); 
        //print_r($query);
        return $qb->getResult();
    }
   

}

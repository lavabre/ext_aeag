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
        return $qb->getOneOrNullResult();
    }

}

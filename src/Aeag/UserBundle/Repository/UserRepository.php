<?php

/**
 * Description of UserRepository
 *
 * @author lavabre
 */

namespace Aeag\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 * @package Aeag\UserBundle\Repository
 */
class UserRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getUsers() {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " order by u.username";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

 
     /**
     * @param $id
     * @return mixed
     */
    public function getUserById($id) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where u.id = " . $id;
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @param $username
     * @return mixed
     */
    public function getUserByUsername($username) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where upper(u.username) = upper('" . $username . "')";
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @param $username, $password
     * @return mixed
     */
    public function getUserByUsernamePassword($username, $password) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where upper(u.username) = upper('" . $username . "')";
        $query = $query . " and u.password = '" . $password. "'";
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @param $username, $prenom
     * @return mixed
     */
    public function getUserByUsernamePrenom($username, $prenom) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where upper(u.username) = upper('" . $username . "')";
        $query = $query . " and upper(u.prenom) = upper('" . $prenom . "')";
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
         
    /**
     * @param $username
     * @return mixed
     */
    public function getUserByCorrespondant($correspondant) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where u.correspondant = " . $correspondant;
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
         return $qb->getResult();
    }
    
    /**
     * @param $username
     * @return mixed
     */
    public function getUserByCorrespondantUnique($correspondant) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where u.correspondant = " . $correspondant;
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    
    
     /**
     * @param $username
     * @return mixed
     */
    public function getUsersByRole($role) {

        $query = "select distinct u";
        $query = $query . " from Aeag\UserBundle\Entity\User u";
        $query = $query . " where u.roles like '%" . $role . "%'";
     
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
    
     
}

<?php

/**
 * Description of UtilisateurRepository
 *
 * @author lavabre
 */

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\EdlBundle\Repository
 */
class UtilisateurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getUtilisateur() {
        $query = "select p";
        $query = $query . " from Aeag\EdlBundle\Entity\Utilisateur p";
        $query = $query . " order by p.username";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getUtilisateurByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\EdlBundle\Entity\Utilisateur p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getUtilisateurByExtid($extId) {
        $query = "select p";
        $query = $query . " from Aeag\EdlBundle\Entity\Utilisateur p";
        $query = $query . " where p.extId = :extId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('extId', $extId);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getUtilisateurByNom($username) {
        $query = "select p";
        $query = $query . " from Aeag\EdlBundle\Entity\Utilisateur p";
        $query = $query . " where p.username = :username";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('username', $username);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getUtilisateurByLoginPassword($login, $pwd) {
        $query = "select p";
        $query = $query . " from Aeag\EdlBundle\Entity\Utilisateur p";
        $query = $query . " where p.username = :login";
        $query = $query . " and p.password = :pwd";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('login', $login);
        $qb->setParameter('pwd', $pwd);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

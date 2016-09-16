<?php

/**
 * Description of StatistiquesRepository
 *
 * @author lavabre
 */

namespace Aeag\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class StatistiquesRepository
 * @package Aeag\UserBundle\Repository
 */
class StatistiquesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getStatistiques() {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " order by u.user";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getStatistiquesById($id) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " where u.id = " . $id;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
      /**
     * @param $id
     * @return mixed
     */
    public function getStatistiquesByIp($ip) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " where u.ip = '" . $ip . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    public function getStatistiquesByUser($userId) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " where u.user = " . $userId;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getStatistiquesByUserDateConnexion($userId, $dateConnexion) {
        
           
        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " where u.user = " . $userId;
        $query = $query . " and  to_char(u.dateDebutConnexion,'YYYY-MM-DD') = '" . $dateConnexion->format('Y-m-d') . "'";

        $qb = $this->_em->createQuery($query);

       //  print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getNbStatistiques() {

        $query = "select sum(u.nbConnexion)";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }
    
     public function getNbConnectes() {

        $query = "select count(u.id)";
        $query = $query . " from Aeag\UserBundle\Entity\Statistiques u";
        $query = $query . " where u.dateFinConnexion is null ";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

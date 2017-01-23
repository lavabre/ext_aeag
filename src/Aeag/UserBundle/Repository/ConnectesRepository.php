<?php

/**
 * Description of ConnectesRepository
 *
 * @author lavabre
 */

namespace Aeag\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ConnectesRepository
 * @package Aeag\UserBundle\Repository
 */
class ConnectesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getConnectes() {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Connectes u";
        $query = $query . " order by u.user";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getConnectesByIp($ip) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Connectes u";
        $query = $query . " where u.ip= :ip";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ip', $ip);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getNbConnectes() {

        $query = "select count(u.ip)";
        $query = $query . " from Aeag\UserBundle\Entity\Connectes u";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getConnectes5Minutes($time5) {

        $query = "select u";
        $query = $query . " from Aeag\UserBundle\Entity\Connectes u";
        $query = $query . " where u.time < :time5";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('time5', $time5);

        //print_r($query);
        return $qb->getResult();
    }

}

<?php

/**
 * Description of PgProgLotPrestaWebusersRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotPrestaWebusersRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotPrestaWebusersRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPrestaWebusers() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPrestaWebusers p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPrestaWebusers p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotPrestaWebusersByPresta($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPrestaWebusers p";
        $query = $query . " where p.presta = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

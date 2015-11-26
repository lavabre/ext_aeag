<?php

/**
 * Description of PgProgWebuserRsxRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgWebuserRsxRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgWebuserRsxRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgWebuserRsx() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " order by p.webuser";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserRsxByReseauMesure($pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " where p.reseauMesure = " . $pgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserRsxByWebuser($pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " where p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgWebuserRsxByWebuserReseauMesure($pgProgWebusers, $pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " where p.reseauMesure = " . $pgRefReseauMesure->getGroupementId();
        $query = $query . " and p.webuser = " . $pgProgWebusers->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

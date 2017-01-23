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
        $query = $query . " where p.reseauMesure = :pgRefReseauMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefReseauMesure', $pgRefReseauMesure->getGroupementId());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserRsxByWebuser($pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " where p.webuser = :pgProgWebuser";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgWebusers', $pgProgWebusers->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgWebuserRsxByWebuserReseauMesure($pgProgWebusers, $pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserRsx p";
        $query = $query . " where p.reseauMesure = :pgRefReseauMesure";
        $query = $query . " and p.webuser = :pgProgWebusers";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgWebusers', $pgProgWebusers->getId());
        $qb->setParameter('pgRefReseauMesure', $pgRefReseauMesure->getGroupementId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

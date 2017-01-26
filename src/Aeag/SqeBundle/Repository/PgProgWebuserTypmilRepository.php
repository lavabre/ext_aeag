<?php

/**
 * Description of PgProgWebuserTypmilRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgWebuserTypmilRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgWebuserTypmilRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgWebuserTypmil() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserTypmil p";
        $query = $query . " order by p.webuser";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserTypmilByTypmil($pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserTypmil p";
        $query = $query . " where p.typmil = :pgProgTypeMilieu";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebuserTypmilByWebuser($pgProgWebusers) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserTypmil p";
        $query = $query . " where p.webuser = :pgProgWebusers";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgWebusers', $pgProgWebusers->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgWebuserTypmilByWebuserTypmil($pgProgWebusers, $pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebuserTypmil p";
        $query = $query . " where p.typmil = :pgProgTypeMilieu";
        $query = $query . " and p.webuser = :pgProgWebusers";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgWebusers', $pgProgWebusers->getId());
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

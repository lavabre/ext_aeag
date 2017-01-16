<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertRecouvRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertRecouvRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdInvertRecouvs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertRecouv c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertRecouvByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertRecouv c";
        $query = $query . " where c.prelev = :pgCmdPrelevHbInvert";
        $query = $query . " order by c.substrat";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelevHbInvert', $pgCmdPrelevHbInvert->getPrelev()->getId());
        //print_r($query . '<br/>');
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertRecouvByPrelevSubstrat($pgCmdPrelevHbInvert, $substrat) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertRecouv c";
        $query = $query . " where c.prelev = :pgCmdPrelevHbInvert";
        $query = $query . " and c.substrat = ':substrat";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelevHbInvert', $pgCmdPrelevHbInvert->getPrelev()->getId());
        $qb->setParameter('substrat', $substrat);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdPrelevHbDiatoRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdPrelevHbDiatoRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdPrelevHbDiatos() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbDiato c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PPgCmdPrelevHbDiato c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelev->getId());
        //print_r($query);
        return $qb->getResult();
    }

}

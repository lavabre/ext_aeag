<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class gCmdMphytListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdMphytUrRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdMphytUrs() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMphytUr c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdMphytUrByPrelev($pgCmdPrelevHbMphyt) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMphytUr c";
        $query = $query . " where c.prelev = :pgCmdPrelevHbMphyt";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelevHbMphyt', $pgCmdPrelevHbMphyt->getPrelev());
        //print_r($query);
        return $qb->getResult();
    }

}

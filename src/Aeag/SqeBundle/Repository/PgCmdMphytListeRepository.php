<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class gCmdMphytListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdMphytListeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdMphytListes() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMphytListe c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdMphytListeByPrelev($pgCmdMphytUr) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdMphytListe c";
        $query = $query . " where c.prelev = :pgCmdMphytUr";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdMphytUr', $pgCmdMphytUr->getPrelev());
        //print_r($query);
        return $qb->getResult();
    }

}

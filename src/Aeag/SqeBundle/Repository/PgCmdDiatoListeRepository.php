<?php

/**
 * Description of PgCmdDiatoListeRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdDiatoListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdDiatoListeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdDiatoListes() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDiatoListe c";
        $query = $query . " order by c.codeSandre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdDiatoListeByPrelev($pgCmdPrelevHbDiato) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdDiatoListe c";
        $query = $query . " where c.prelev = :pgCmdPrelev";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelev', $pgCmdPrelevHbDiato->getPrelev()->getId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

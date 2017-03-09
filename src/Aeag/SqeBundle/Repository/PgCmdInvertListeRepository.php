<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertListeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertListeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdInvertListes() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertListe c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertListeByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertListe c";
        $query = $query . " where c.prelev = :pgCmdPrelevHbInvert";
        $query = $query . " order by  c.codeSandre, c.phase,c.prelem";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelevHbInvert', $pgCmdPrelevHbInvert->getPrelev()->getId());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertListeByPrelevPrelemPhaseCodeSandre($pgCmdPrelevHbInvert, $prelem, $phase, $codeSandre) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertListe c";
        $query = $query . " where c.prelev = :pgCmdPrelevHbInvert";
        $query = $query . " and c.prelem = :prelem";
        $query = $query . " and c.phase = :phase";
        $query = $query . " and c.codeSandre = :codeSandre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgCmdPrelevHbInvert', $pgCmdPrelevHbInvert->getPrelev()->getId());
        $qb->setParameter('prelem', $prelem);
        $qb->setParameter('phase', $phase);
        $qb->setParameter('codeSandre', $codeSandre);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

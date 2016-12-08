<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdInvertPrelemRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdInvertPrelemRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgCmdInvertPrelems() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertPrelem c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertPrelemByPrelev($pgCmdPrelevHbInvert) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertPrelem c";
        $query = $query . " where c.prelev = " . $pgCmdPrelevHbInvert->getPrelev()->getId();
        $query = $query . " order by c.prelem";
        $qb = $this->_em->createQuery($query);
        print_r($query . '<br/>');
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgCmdInvertPrelemByPrelevPrelem($pgCmdPrelevHbInvert, $prelem) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdInvertPrelem c";
        $query = $query . " where c.prelev = " . $pgCmdPrelevHbInvert->getPrelev()->getId();
        $query = $query . " and c.prelem = '" . $prelem . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgRefCorresPrestaRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefCorresPrestas() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresPresta p";
        $query = $query . " order by p.nomCorres";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefCorresPrestaByAdrCorId($adrCorId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresPresta p";
        $query = $query . " where p.adrCorId = " . $adrCorId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefCorresPrestaByAncnum($ancnum) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresPresta p";
        $query = $query . " where p.ancnum = '" . $ancnum . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefCorresPrestaByCodeSiret($codeSiret) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresPresta p";
        $query = $query . " where p.codeSiret = '" . $codeSiret . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefCorresPrestaByCodeSandre($codeSandre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresPresta p";
        $query = $query . " where p.codeSandre = '" . $codeSandre . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

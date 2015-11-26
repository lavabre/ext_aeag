<?php

/**
 * Description of PgProgLotAnRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotAnRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " order by p.anneeProg, p_version";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotAnByanneeProg($anneeProg) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        if ($anneeProg) {
            $query = $query . " where p.anneeProg = " . $anneeProg;
        }
        $query = $query . " order by p.anneeProg, p.version";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByLot($pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.lot = " . $pgProgLot->getId();
        $query = $query . " order by p.anneeProg, p.version";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotAnByAnneeLot($annee, $pgProgLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = " . $annee;
        $query = $query . " and p.lot = " . $pgProgLot->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotAnByLotVersion($pgProgLot, $version) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.lot = " . $pgProgLot->getId();
        $query = $query . " and p.version = " . $version;
        $query = $query . " order by p.anneeProg, p.version";
        $qb = $this->_em->createQuery($query);
       // print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotAnByAnneeLotVersion($annee, $pgProgLot, $version) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = " . $annee;
        $query = $query . " and p.lot = " . $pgProgLot->getId();
        $query = $query . " and p.version = " . $version;
        $qb = $this->_em->createQuery($query);
       //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getMaxVersionByAnneeLot($annee, $pgProgLot) {
        $query = "select max(p.version)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotAn p";
        $query = $query . " where p.anneeProg = " . $annee;
        $query = $query . " and p.lot = " . $pgProgLot->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

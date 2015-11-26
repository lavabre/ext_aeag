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
class PgProgLotStationAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotStationAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotStationAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotStationAnBylotan($PgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByStation($PgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.station = " . $PgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnStation($PgProgLotAn, $PgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.station = " . $PgRefStationMesure->getOuvFoncId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotStationAnByReseau($PgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.rsxId = " . $PgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnReseau($PgProgLotAn, $PgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.rsxId = " . $PgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnStationReseau($PgProgLotAn, $PgRefStationMesure, $PgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.station = " . $PgRefStationMesure->getOuvFoncId();
        $query = $query . " and p.rsxId = " . $PgRefReseauMesure->getGroupementId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     public function getPgRefReseauMesuresByLotAn($PgProgLotAn) {
        $query = "select distinct(r)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure r";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $query = $query . " and p.rsxId = r.groupementId";
        $query = $query . " order by r.codeSandre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function countPgProgLotStationAnByLotan($PgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = " . $PgProgLotAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

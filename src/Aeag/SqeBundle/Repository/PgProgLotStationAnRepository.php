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
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotStationAnBylotan($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByStation($pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.station = :pgRefStationMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.station = :pgRefStationMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotStationAnByReseau($pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.rsxId = :pgRefReseauMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefReseauMesure', $pgRefReseauMesure->getGroupementId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnReseau($pgProgLotAn, $pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.rsxId = :pgRefReseauMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgRefReseauMesure', $pgRefReseauMesure->getGroupementId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotStationAnByLotAnStationReseau($pgProgLotAn, $pgRefStationMesure, $pgRefReseauMesure) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.station = :pgRefStationMesure";
        $query = $query . " and p.rsxId = :pgRefReseauMesure";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgRefStationMesure', $pgRefStationMesure->getOuvFoncId());
        $qb->setParameter('pgRefReseauMesure', $pgRefReseauMesure->getGroupementId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefReseauMesuresByLotAn($pgProgLotAn) {
        $query = "select distinct(r)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure r";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.rsxId = r.groupementId";
        $query = $query . " order by r.codeSandre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function countPgProgLotStationAnByLotan($pgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotStationAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

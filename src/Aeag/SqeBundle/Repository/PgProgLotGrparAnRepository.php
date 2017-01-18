<?php

/**
 * Description of PgProgLotGrparAnRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotGrparAnRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotGrparAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotGrparAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " order by p.lot";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.id =:id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotGrparAnByLotan($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnByGrpparref($pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotGrparAnByPrestataire($pgProgLotAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " order by p.prestaDft";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnByLotAnPrestaDft($pgProgLotAn, $prestaDft) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.prestaDft = :prestaDft";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('prestaDft', $prestaDft->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotGrparAnByLotAnPrestaDftGrpparref($pgProgLotAn, $prestaDft, $pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.prestaDft = :prestaDft";
        $query = $query . " and p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('prestaDft', $prestaDft->getAdrCorId());
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function countPgProgLotGrparAnByLotan($pgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function countPgProgLotGrparAnByValide($pgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.valide = 'O'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function countPgProgLotGrparAnByNonValide($pgProgLotAn) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotGrparAn p";
        $query = $query . " where p.lotan = :pgProgLotAn";
        $query = $query . " and p.valide = 'N'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

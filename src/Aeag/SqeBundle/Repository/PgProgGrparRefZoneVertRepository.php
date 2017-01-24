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
class PgProgGrparRefZoneVertRepository extends EntityRepository {

    public function getPgProgGrparRefZoneVertByGrparRef($pgProgGrpParamRef) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgProgGrparRefZoneVertByGrparRef($pgProgGrpParamRef) {
        $query = "select count(p)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.grparRef = :pgProgGrpParamRef";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgProgGrparRefZoneVertByPgSandreZoneVerticaleProspectee($pgSandreZoneVerticaleProspectee) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.pgSandreZoneVerticaleProspectee = :pgSandreZoneVerticaleProspectee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreZoneVerticaleProspectee', $pgSandreZoneVerticaleProspectee->getCodeZone());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgGrpParamRef, $pgSandreZoneVerticaleProspectee) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.pgProgGrpParamRef = :pgProgGrpParamRef";
        $query = $query . " and p.pgSandreZoneVerticaleProspectee = :pgSandreZoneVerticaleProspectee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        $qb->setParameter('pgSandreZoneVerticaleProspectee', $pgSandreZoneVerticaleProspectee->getCodeZone());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgGrpParamRef, $pgSandreZoneVerticaleProspectee) {
        $query = "select count(p.typClassProf)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrparRefZoneVert p";
        $query = $query . " where p.pgProgGrpParamRef = :pgProgGrpParamRef";
        $query = $query . " and p.pgSandreZoneVerticaleProspectee = :pgSandreZoneVerticaleProspectee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgGrpParamRef', $pgProgGrpParamRef->getid());
        $qb->setParameter('pgSandreZoneVerticaleProspectee', $pgSandreZoneVerticaleProspectee->getCodeZone());
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

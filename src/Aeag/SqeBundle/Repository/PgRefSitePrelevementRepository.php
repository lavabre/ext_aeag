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
class PgRefSitePrelevementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefSitePrelevements() {
        $query = "select distinct p.codeSite, p.nomSite";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " order by p.codeSite";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefSitePrelevementById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefSitePrelevementByCodeSite($codeSite) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.codeSite = :codeSite";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeSite', $codeSite);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgRefSitePrelevementByCodeSite($codeSite) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.codeSite = :codeSite";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeSite', $codeSite);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgRefSitePrelevementByOuvFoncId($ouvFoncId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.ouvFonc = :ouvFoncId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvFoncId', $ouvFoncId);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPgRefSitePrelevementByOuvFoncId($ouvFoncId) {
        $query = "select count(p.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.ouvFonc = :ouvFoncId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvFoncId', $ouvFoncId);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgRefSitePrelevementByCodeSupport($pgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefSitePrelevement p";
        $query = $query . " where p.codeSupport = :pgSandreSupports";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgSandreSupports', $pgSandreSupports->getCodeSupport());
        //print_r($query);
        return $qb->getResult();
    }

}

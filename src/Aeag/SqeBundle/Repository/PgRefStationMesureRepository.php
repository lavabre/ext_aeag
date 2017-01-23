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
class PgRefStationMesureRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefStationMesures() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " order by p.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->useResultCache(true)->setResultCacheLifetime(3600)->getResult();
    }

    /**
     * @return array
     */
    public function getSqeRefStationMesureSitePrelevements() {
        $query = "select p.ouvFoncId, p.code, p.libelle, p.type, p.nomCommune, p.nomCoursEau, p.nomMasdo, count(s.id) nbSites";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " , Aeag\SqeBundle\Entity\PgRefSitePrelevement s";
        $query = $query . " where s.ouvFonc = p.ouvFoncId";
        $query = $query . " group by p.ouvFoncId, p.code, p.libelle, p.type, p.nomCommune, p.nomCoursEau, p.nomMasdo";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->useResultCache(true)->setResultCacheLifetime(3600)->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefStationMesureByOuvFoncId($ouvFoncId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " where p.ouvFoncId = :ouvFoncId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvFoncId', $ouvFoncId);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getNbPgRefStationMesureByOuvFoncId($ouvFoncId) {
        $query = "select count(p.ouvFoncId)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " where p.ouvFoncId = :ouvFoncId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvFoncId', $ouvFoncId);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getPgRefStationMesureByNumero($numero) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " where p.numero = :numero";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('numero', $numero);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefStationMesureByCode($code) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefStationMesure p";
        $query = $query . " where p.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

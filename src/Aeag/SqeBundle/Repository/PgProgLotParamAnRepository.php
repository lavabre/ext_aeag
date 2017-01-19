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
class PgProgLotParamAnRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotParamAn() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " order by p.codeParametre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotParamAnById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamAnByRsxId($rsxId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.rsx = :rsxId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('rsxId', $rsxId);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByPrestataire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByGrparan($pgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = :pgProgLotGrparAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotGrparAn', $pgProgLotGrparAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByGrparAnCodeParametre($pgProgLotGrparAn, $pgSandreParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = :pgProgLotGrparAn";
        $query = $query . " and p.codeParametre = :pgSandreParametre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotGrparAn', $pgProgLotGrparAn->getId());
        $qb->setParameter('pgSandreParametre', $pgSandreParametre->getCodeParametre());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamAnByPrestataireGrparan($pgRefCorresPresta, $pgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = :pgRefCorresPresta";
        $query = $query . " and p.grparan = :pgProgLotGrparAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        $qb->setParameter('pgProgLotGrparAn', $pgProgLotGrparAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPrestatairesByGrparan($pgProgLotGrparAn) {
        $query = "select distinct(p.prestataire)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = :pgProgLotGrparAn";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotGrparAn', $pgProgLotGrparAn->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbProgLotParamAnEnvSituByStationAnPeriodeAnPrestataire($pgProgLotStationAn, $pgProglotPeriodeAn, $prestataire) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotPeriodeProg per";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgGrpParamRef grou";
        $query = $query . " where per.stationAn = :pgProgLotStationAn";
        $query = $query . " and per.periodan = :pgProglotPeriodeAn";
        $query = $query . " and par.grparan  = per.grparAn";
        $query = $query . " and par.prestataire  = :prestataire";
        $query = $query . " and gran.id  = per.grparAn";
        $query = $query . " and grou.id  = gran.grparRef";
        $query = $query . " and grou.typeGrp  in ('ENV','SIT')";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotStationAn', $pgProgLotStationAn->getId());
        $qb->setParameter('pgProglotPeriodeAn', $pgProglotPeriodeAn->getId());
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbProgLotParamAnAnaByStationAnPeriodeAnPrestataire($pgProgLotStationAn, $pgProglotPeriodeAn, $prestataire) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotPeriodeProg per";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgGrpParamRef grou";
        $query = $query . " where per.stationAn = :pgProgLotStationAn";
        $query = $query . " and per.periodan = :pgProglotPeriodeAn";
        $query = $query . " and par.grparan  = per.grparAn";
        $query = $query . " and par.prestataire  = :prestataire";
        $query = $query . " and gran.id  = per.grparAn";
        $query = $query . " and grou.id  = gran.grparRef";
        $query = $query . " and grou.typeGrp  = 'ANA'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotStationAn', $pgProgLotStationAn->getId());
        $qb->setParameter('pgProglotPeriodeAn', $pgProglotPeriodeAn->getId());
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbDoublonsByLotanParametre($pgProgLotAn, $pgProgLotGrparAn, $pgProgGrparRefLstParam) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotAn lotan";
        $query = $query . " where lotan.id = :pgProgLotAn";
        $query = $query . " and gran.lotan  = lotan.id";
        $query = $query . " and par.grparan  = gran.id";
        $query = $query . " and par.grparan  <> :pgProgLotGrparAn";
        $query = $query . " and par.codeParametre = :parametre";
        if ($pgProgGrparRefLstParam->getCodeFraction()) {
            $query = $query . " and par.codeFraction = :fraction";
        }
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgLotAn', $pgProgLotAn->getId());
        $qb->setParameter('pgProgLotGrparAn', $pgProgLotGrparAn->getId());
        $qb->setParameter('parametre', $pgProgGrparRefLstParam->getCodeParametre()->getCodeParametre());
        if ($pgProgGrparRefLstParam->getCodeFraction()) {
            $qb->setParameter('fraction', $pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
        }
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

}

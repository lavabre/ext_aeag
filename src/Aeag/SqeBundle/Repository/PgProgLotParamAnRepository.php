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
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamAnByRsxId($rsxId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.rsx = " . $rsxId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByPrestataire($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByGrparan($PgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotParamAnByGrparAnCodeParametre($PgProgLotGrparAn, $PgSandreParametre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $query = $query . " and p.codeParametre = '" . $PgSandreParametre->getCodeParametre() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotParamAnByPrestataireGrparan($PgRefCorresPresta, $PgProgLotGrparAn) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.prestataire = " . $PgRefCorresPresta->getAdrCorId();
        $query = $query . " and p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPrestatairesByGrparan($PgProgLotGrparAn) {
        $query = "select distinct(p.prestataire)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn p";
        $query = $query . " where p.grparan = " . $PgProgLotGrparAn->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbProgLotParamAnEnvSituByStationAnPeriodeAnPrestataire($PgProgLotStationAn, $PgProglotPeriodeAn, $prestataire) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotPeriodeProg per";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgGrpParamRef grou";
        $query = $query . " where per.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and per.periodan = " . $PgProglotPeriodeAn->getId();
        $query = $query . " and par.grparan  = per.grparAn";
        $query = $query . " and par.prestataire  = " . $prestataire->getAdrCorId();
        $query = $query . " and gran.id  = per.grparAn";
        $query = $query . " and grou.id  = gran.grparRef";
        $query = $query . " and grou.typeGrp  in ('ENV','SIT')";
        $qb = $this->_em->createQuery($query);
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbProgLotParamAnAnaByStationAnPeriodeAnPrestataire($PgProgLotStationAn, $PgProglotPeriodeAn, $prestataire) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotPeriodeProg per";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgGrpParamRef grou";
        $query = $query . " where per.stationAn = " . $PgProgLotStationAn->getId();
        $query = $query . " and per.periodan = " . $PgProglotPeriodeAn->getId();
        $query = $query . " and par.grparan  = per.grparAn";
        $query = $query . " and par.prestataire  = " . $prestataire->getAdrCorId();
        $query = $query . " and gran.id  = per.grparAn";
        $query = $query . " and grou.id  = gran.grparRef";
        $query = $query . " and grou.typeGrp  = 'ANA'";
        $qb = $this->_em->createQuery($query);
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getNbDoublonsByLotanParametre($pgProgLotAn, $pgProgLotGrparAn, $pgProgGrparRefLstParam) {
        $query = "select count(par.id)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotParamAn par";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotGrparAn gran";
        $query = $query . " , Aeag\SqeBundle\Entity\PgProgLotAn lotan";
        $query = $query . " where lotan.id = " . $pgProgLotAn->getId();
        $query = $query . " and gran.lotan  = lotan.id";
        $query = $query . " and par.grparan  = gran.id";
        $query = $query . " and par.grparan  <> "  . $pgProgLotGrparAn->getId();
        $query = $query . " and par.codeParametre = '" . $pgProgGrparRefLstParam->getCodeParametre()->getCodeParametre() . "'";
        if ($pgProgGrparRefLstParam->getCodeFraction()) {
            $query = $query . " and par.codeFraction = '" . $pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction() . "'";
        }
        $qb = $this->_em->createQuery($query);
        // print_r($query);
        return $qb->getSingleScalarResult();
    }

}

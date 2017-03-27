<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;
use DoctrineExtensions\Query\Postgresql;

/**
 * Class FraisDeplacementRepository
 * @package Aeag\FrdBundle\Repository
 */
class FraisDeplacementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getListeFraisDeplacementAll() {

        $query = "select f";
        $query = $query . "  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = u.id ";
        $query = $query . " order by f.dateDepart, f.heureDepart, f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getListeFraisDeplacementByUser($userId, $annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :anne";
        $query = $query . " order by  f.dateDepart Desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementById($id) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.id = :id";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        return $qb->getOneOrNullResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByIdUser($id, $userId) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.id = :id";
        $query = $query . " and f.user = :userId";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        $qb->setParameter('userId', $userId);
        return $qb->getOneOrNullResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByAnnees($annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where to_char(f.dateDepart,'YYYYMMDD') >= :annee";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByAnnee($anneeDeb, $anneeFin) {


        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') <= ':anneeFin";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserAnnee($userId, $anneeDeb, $anneeFin) {


        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') <= :anneeFin";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getAnnees() {


        $query = "select distinct f.dateDepart";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " order by f.dateDepart";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getUserAnnees($userId) {


        $query = "select distinct f.dateDepart";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " order by f.dateDepart";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserDateDepart($userId, $dateDepart, $heureDepart) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and to_char(f.dateDepart,'YYYY-MM-DD') = :dateDepart";
        $query = $query . " and f.heureDepart = :heureDepart";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('dateDepart', $dateDepart->format('Y-m-d'));
        $qb->setParameter('heureDepart', $heureDepart);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserDate($userId, $dateDepart, $heureDepart, $dateRetour, $heureRetour) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and to_char(f.dateDepart,'YYYY-MM-DD') = :dateDepart";
        $query = $query . " and f.heureDepart = :heureDepart";
        $query = $query . " and to_char(f.dateRetour,'YYYY-MM-DD') = :dateRetour";
        $query = $query . " and f.heureRetour = :heureRetour";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('dateDepart', $dateDepart->format('Y-m-d'));
        $qb->setParameter('heureDepart', $heureDepart);
        $qb->setParameter('dateRetour', $dateRetour->format('Y-m-d'));
        $qb->setParameter('heureRetour', $heureRetour);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserPhase($userId, $phaseId, $annee) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and f.phase =  :phaseId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :annee";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementEnCoursByUserPhase($userId, $phaseId, $anneeDeb, $anneeFin) {

        $query = "select count(f.id)";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = :userId";
        $query = $query . " and f.phase <=  :phaseId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateRetour,'YYYYMMDD') <= :anneeFin";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getSingleScalarResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementEnCoursByUserPhase($userId, $phaseId, $anneeDeb, $anneeFin) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user =:userId";
        $query = $query . " and f.phase <=  :phaseId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateRetour,'YYYYMMDD') <= :anneeFin";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        // print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('userId', $userId);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementEnCoursByPhase($phaseId, $anneeDeb, $anneeFin) {

        $query = "select count(f.id)";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.phase <=  :phaseId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateRetour,'YYYYMMDD') <= :anneeFin";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getSingleScalarResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementEnCoursByPhase($phaseId, $anneeDeb, $anneeFin) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.phase <=  :phaseId";
        $query = $query . " and  to_char(f.dateDepart,'YYYYMMDD') >= :anneeDeb";
        $query = $query . " and to_char(f.dateRetour,'YYYYMMDD') <= :anneeFin";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        // print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('anneeDeb', $anneeDeb->format('Ymd'));
        $qb->setParameter('anneeFin', $anneeFin->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByPhase($phaseId, $annee) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = u.id";
        $query = $query . " and f.phase =  :phaseId";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :annee";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phaseId', $phaseId);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementaExporter($annee) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " , Aeag\FrdBundle\Entity\Phase p";
        $query = $query . " where f.phase =  p.id";
        $query = $query . " and p.code = '30'";
        $query = $query . " and f.valider = 'O'";
        $query = $query . " and (f.exporter =  'N' or f.exporter is null)";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :annee";
        $query = $query . " order by f.user, f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getDestinataireaExporter($annee) {

        $query = "select distinct f.user";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " , Aeag\FrdBundle\Entity\Phase p";
        $query = $query . " where f.phase =  p.id";
        $query = $query . " and p.code > '30'";
        $query = $query . " and (f.exporter =  'N' or f.exporter is null)";
        $query = $query . " and to_char(f.dateDepart,'YYYYMMDD') >= :annee";
        $query = $query . " order by  f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee->format('Ymd'));
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementByEtfrId($etfrId) {

        $query = "select count(f.id)";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.etfrId = :etfrId";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('etfrId', $etfrId);
        return $qb->getSingleScalarResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByEtfrId($etfrId) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        if ($etfrId) {
            $query = $query . " where f.etfrId = :etfrId";
        } else {
            $query = $query . " where f.etfrId is null";
        }
        $query = $query . " order by  f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('etfrId', $etfrId);
        return $qb->getResult();
    }

}

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
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.dateDepart >= '" . $annee->format('Ymd') . "'";
        $query = $query . " order by  f.dateDepart Desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementById($id) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.id = " . $id;

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getOneOrNullResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByIdUser($id, $userId) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.id = " . $id;
        $query = $query . " and f.user = " . $userId;

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getOneOrNullResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByAnnees($annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.dateDepart >= '" . $annee->format('Ymd') . "'";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByAnnee($anneeDeb, $anneeFin) {


        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateDepart <= '" . $anneeFin->format('Ymd') . "'";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserAnnee($userId, $anneeDeb, $anneeFin) {


        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateDepart <= '" . $anneeFin->format('Ymd') . "'";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
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
        $query = $query . " where f.user = " . $userId;
        $query = $query . " order by f.dateDepart";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserDateDepart($userId, $dateDepart, $heureDepart) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.dateDepart = '" . $dateDepart->format('Y-m-d') . "'";
        $query = $query . " and f.heureDepart = '" . $heureDepart . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserDate($userId, $dateDepart, $heureDepart, $dateRetour, $heureRetour) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.dateDepart = '" . $dateDepart->format('Y-m-d') . "'";
        $query = $query . " and f.heureDepart = '" . $heureDepart . "'";
        $query = $query . " and f.dateRetour = '" . $dateRetour->format('Y-m-d') . "'";
        $query = $query . " and f.heureRetour = '" . $heureRetour . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getFraisDeplacementByUserPhase($userId, $phaseId, $annee) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.phase =  '" . $phaseId . "'";
        $query = $query . " and f.dateDepart >= '" . $annee->format('Ymd') . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementEnCoursByUserPhase($userId, $phaseId, $anneeDeb, $anneeFin) {

        $query = "select count(f.id)";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.phase <=  '" . $phaseId . "'";
        $query = $query . " and f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateRetour <= '" . $anneeFin->format('Ymd') . "'";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getSingleScalarResult();
    }
    
     /**
     *
     * @return array
     */
    public function getFraisDeplacementEnCoursByUserPhase($userId, $phaseId, $anneeDeb, $anneeFin) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.user = " . $userId;
        $query = $query . " and f.phase <=  '" . $phaseId . "'";
        $query = $query . " and  f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateRetour <= '" . $anneeFin->format('Ymd') . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
       // print_r($query);
        $qb = $this->_em->createQuery($query);
       return $qb->getResult();
    }
    
    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementEnCoursByPhase($phaseId, $anneeDeb, $anneeFin) {

        $query = "select count(f.id)";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
       $query = $query . " where f.phase <=  '" . $phaseId . "'";
        $query = $query . " and f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateRetour <= '" . $anneeFin->format('Ymd') . "'";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getSingleScalarResult();
    }
    
     /**
     *
     * @return array
     */
    public function getFraisDeplacementEnCoursByPhase($phaseId, $anneeDeb, $anneeFin) {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\FraisDeplacement f";
       $query = $query . " where f.phase <=  '" . $phaseId . "'";
        $query = $query . " and  f.dateDepart >= '" . $anneeDeb->format('Ymd') . "'";
        $query = $query . " and f.dateRetour <= '" . $anneeFin->format('Ymd') . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
       // print_r($query);
        $qb = $this->_em->createQuery($query);
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
        $query = $query . " and f.phase =  '" . $phaseId . "'";
        $query = $query . " and f.dateDepart >= '" . $annee->format('Ymd') . "'";
        $query = $query . " order by f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
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
        $query = $query . " and f.dateDepart >= '" . $annee->format('Ymd') . "'";
        $query = $query . " order by f.user, f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
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
        $query = $query . " and f.dateDepart >= '" . $annee->format('Ymd') . "'";
        $query = $query . " order by  f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getNbFraisDeplacementByEtfrId($etfrId) {

        $query = "select count(f.id)";
        $query = $query . " from  Aeag\FrdBundle\Entity\FraisDeplacement f";
        $query = $query . " where f.etfrId = " . $etfrId;

        //print_r($query);
        $qb = $this->_em->createQuery($query);
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
            $query = $query . " where f.etfrId = " . $etfrId;
        } else {
            $query = $query . " where f.etfrId is null";
        }
        $query = $query . " order by  f.dateDepart desc , f.heureDepart desc , f.dateRetour, f.heureRetour";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

}

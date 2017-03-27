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
 * Class EtatFraisRepository
 * @package Aeag\FrdBundle\Repository
 */
class EtatFraisRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getListeEtatFraisAll() {

        $query = "select f";
        $query = $query . " from Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " order by f.annee, f.num";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getListeEtatFraisByCorrespondantAnnee($corId, $annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " where f.corId = :corId";
        $query = $query . " and f.annee = :annee";
        $query = $query . " order by f.num";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('corId', $corId);
        $qb->setParameter('annee', $annee);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getListeEtatFraisByAnnee($annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " where f.annee = :annee";
        $query = $query . " order by f.num";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getEtatFraisById($id) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
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
    public function getEtatFraisByCorId($corId) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " where f.corId = :corId";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('corId', $corId);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getEtatFraisByAnnees($annee) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " where f.annee = :annee";
        $query = $query . " order by f.annee, f.num";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getAnnees() {


        $query = "select distinct f.annee";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " order by f.annee";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

    /**
     *
     * @return array
     */
    public function getAnneesByCorId($corId) {

        $query = "select distinct f.annee";
        $query = $query . " from  Aeag\FrdBundle\Entity\EtatFrais f";
        $query = $query . " where f.corId = :corId";
        $query = $query . " order by f.annee";

        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('corId', $corId);
        return $qb->getResult();
    }

}

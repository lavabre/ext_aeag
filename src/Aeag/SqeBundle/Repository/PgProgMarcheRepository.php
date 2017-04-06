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
class PgProgMarcheRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgMarches() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.nomMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgMarcheByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByNomMarche($nomMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarches p";
        $query = $query . " where p.nomMarche = :nomMarche";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('nomMarche', $nomMarche);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByTypeMarche($typeMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.typeMarche = :typeMarche";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('typeMarche', $typeMarche);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgMarchesType() {
        $query = "select distinct p.typeMarche";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.typeMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getAvancementHydrobioGlobal($annee_prog) {

        $query = "select * from sqe_avancement_hydrobio_global(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementHydrobioSupport($annee_prog) {

        $query = "select * from sqe_avancement_hydrobio_support(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementHydrobioLot($annee_prog) {

        $query = "select * from sqe_avancement_hydrobio_lot(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementHydrobioStation($annee_prog) {

        $query = "select * from sqe_avancement_hydrobio_station(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementAnalyseGlobal($annee_prog) {

        $query = "select * from sqe_avancement_analyse_global(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementAnalysePeriode($annee_prog) {

        $query = "select * from sqe_avancement_analyse_periode(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementAnalyseLot($annee_prog) {

        $query = "select * from sqe_avancement_analyse_lot(:annee_prog)";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('annee_prog', $annee_prog);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array
     */
    public function getAvancementPrelevementGlobal() {

        $query = "select * from sqe_avancement_prelevement_global()";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

    /**
     * @return array
     */
    public function getAvancementPrelevementTypeMarche() {

        $query = "select * from sqe_avancement_prelevement_type_marche()";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

    /**
     * @return array
     */
    public function getAvancementPrelevementTypeMilieu() {

        $query = "select * from sqe_avancement_prelevement_type_milieu()";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

    /**
     * @return array
     */
    public function getAvancementProgrammationGlobal($annee_prog) {

        $query = "select * from sqe_avancement_Programmation_global(" . $annee_prog . ")";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

    /**
     * @return array
     */
    public function getAvancementProgrammationMarche() {

        $query = "select * from sqe_avancement_Programmation_marche() ";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

    /**
     * @return array
     */
    public function getAvancementProgrammationMilieu() {

        $query = "select * from sqe_avancement_Programmation_milieu() ";
        return $this->getEntityManager('')
                        ->getConnection()
                        ->query($query);
    }

}

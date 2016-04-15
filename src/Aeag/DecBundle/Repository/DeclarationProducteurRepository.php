<?php

/**
 * Description of DeclarationProducteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeclarationProducteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDeclarationProducteurs() {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDeclarationProducteursByAnnee($annee) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.annee = " . $annee;
        $query = $query . " order by d.Producteur  ";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDeclarationProducteurByProducteurAnnee($producteur, $annee) {

        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.Producteur = " . $producteur;
        $query = $query . " and d.annee = " . $annee;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
//     public function getDeclarationProducteursByProducteurAnnee($producteur, $annee) {
//
//        $query = "select d";
//        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
//        $query = $query . " where d.Producteur = " . $producteur;
//        $query = $query . " and d.annee = " . $annee;
//
//        $qb = $this->_em->createQuery($query);
//
//        //print_r($query);
//        return $qb->getResult();
//    }

    public function getQuantiteDeclarationProducteurByProducteurAnnee($producteur, $annee) {

        $query = "select sum(d.quantiteReel)";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.Producteur = " . $producteur;
        $query = $query . " and d.annee = " . $annee;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getDeclarationProducteurByProducteur($producteur) {

        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.Producteur = " . $producteur;
        $query = $query . " order by d.annee";
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    public function getDeclarationProducteurById($id) {

        $query = "select  d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.id = " . $id;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCountStatutByProducteurAnneeStatut($producteur, $annee, $statut) {

        $query = "select count(d.id)";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationProducteur d";
        $query = $query . " where d.Producteur = " . $producteur;
        $query = $query . " and d.annee = " . $annee;
        $query = $query . " and d.statut = '" . $statut . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

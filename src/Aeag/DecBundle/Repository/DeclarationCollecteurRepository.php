<?php

/**
 * Description of DeclarationCollecteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeclarationCollecteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDeclarationCollecteurs() {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " order by d.Collecteur, d.annee";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDeclarationCollecteursByAnnee($annee) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.annee = :annee ";
        $query = $query . " order by d.Collecteur";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDeclarationCollecteursByAnneeStatut($annee, $statut) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.annee = :annee";
        $query = $query . " and d.statut =  :statut";
        $query = $query . " order by d.Collecteur";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('statut', $statut);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDeclarationCollecteurByCollecteur($collecteur) {

        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.Collecteur =  :collecteur";
        $query = $query . " order by d.annee";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('collecteur', $collecteur);

        //print_r($query);
        return $qb->getResult();
    }

    public function getDeclarationCollecteurByCollecteurAnnee($collecteur, $annee) {

        $query = "select distinct d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.Collecteur = :collecteur";
        $query = $query . " and d.annee = :annee";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('collecteur', $collecteur);
        $qb->setParameter('annee', $annee);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getDeclarationCollecteurByCollecteurAnneeStatut($collecteur, $annee, $statut) {

        $query = "select distinct d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.Collecteur = :collecteur";
        $query = $query . " and d.annee = :annee";
        $query = $query . " and d.statut = :statut";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('collecteur', $collecteur);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('statut', $statut);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getDeclarationCollecteurById($id) {

        $query = "select  d";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " where d.id = :id";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getAnnees() {

        $query = "select distinct(d.annee) as annee";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d";
        $query = $query . " order by d.annee";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

}

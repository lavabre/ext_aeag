<?php

/**
 * Description of DeclarationCollecteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SousDeclarationCollecteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getSousDeclarationCollecteurByDeclarationCollecteur($declarationCollecteur) {
        $query = "select sd";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.DeclarationCollecteur = " . $declarationCollecteur;
        $query = $query . " order by sd.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getSousDeclarationCollecteurByDeclarationCollecteurNumero($declarationCollecteur, $numero) {
        $query = "select sd";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.DeclarationCollecteur = " . $declarationCollecteur;
        $query = $query . " and sd.numero = " . $numero;
        $query = $query . " order by sd.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getSousDeclarationCollecteurByDeclarationCollecteurStatut($declarationCollecteur, $statut) {
        $query = "select sd";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.DeclarationCollecteur = " . $declarationCollecteur;
        $query = $query . " and sd.statut = '" . $statut . "'";
        $query = $query . " order by sd.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    
     /**
     * @return array
     */
    public function getSousDeclarationCollecteurById($id) {
        $query = "select sd";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return value
     */
    public function getNumeroLibreById($declarationCollecteur) {
        $query = "select max(sd.numero) + 1";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.DeclarationCollecteur = " . $declarationCollecteur;
        $qb = $this->_em->createQuery($query);
        // print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    /**
     * @return value
     */
    public function getMaxNumero($annee) {
        $query = "select max(sd.numero)";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationCollecteur d,";
        $query = $query . " Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . " where sd.DeclarationCollecteur = d.id ";
        $query = $query . " and d.annee = " . $annee;
        $qb = $this->_em->createQuery($query);
        // print_r($query);
        return $qb->getSingleScalarResult();
    }
    
    
     public function getCountStatutByDeclarationCollecteurStatut($declarationCollecteur, $statut) {

        $query = "select count(d.id)";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur d";
        $query = $query . " where d.DeclarationCollecteur = " . $declarationCollecteur;
        $query = $query . " and d.statut = '" . $statut . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

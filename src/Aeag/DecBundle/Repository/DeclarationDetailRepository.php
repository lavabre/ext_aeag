<?php

/**
 * Description of DossierTrimestreDetailRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeclarationDetailRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDeclarationDetails() {
        $query = "select dd";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " from Aeag\DecBundle\Entity\SousDeclarationCollecteur sd";
        $query = $query . "   , Aeag\DecBundle\Entity\DeclarationCollecteur dc";
        $query = $query . "   , Aeag\DecBundle\Entity\DeclarationProducteur dp";
        $query = $query . "   , Aeag\DecBundle\Entity\Ouvrage col";
        $query = $query . "   , Aeag\DecBundle\Entity\Ouvrage prod";
        $query = $query . " where dd.SousDeclarationCollecteur = sd.id";
        $query = $query . " and sd.DeclarationCollecteur = dc.id";
        $query = $query . " and dc.Collecteur = col.id";
        $query = $query . " and sd.DeclarationProducteur = dpc.id";
        $query = $query . " and dp.Producteur = prod.id";
        $query = $query . " order by col.numero, dc.annee, sd.numero, prod.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur) {
        $query = "select dd";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.SousDeclarationCollecteur = :sousDeclarationCollecteur";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('sousDeclarationCollecteur', $sousDeclarationCollecteur);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDeclarationDetailsByDeclarationProducteur($declarationProducteur) {
        $query = "select dd";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.DeclarationProducteur = :declarationProducteur";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('declarationProducteur', $declarationProducteur);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDeclarationDetail($sousDeclarationCollecteur, $declarationProducteur, $dechet, $filiere, $traitFiliere, $numFacture, $quantiteReel, $montReel, $nature, $dateFacture) {
        $query = "select dd";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.SousDeclarationCollecteur = :sousDeclarationCollecteur";
        $query = $query . " and dd.DeclarationProducteur = :declarationProducteur";
        $query = $query . " and dd.Dechet = :dechet";
        $query = $query . " and dd.Filiere = :filiere";
        $query = $query . " and dd.traitFiliere = :traitFiliere";
        $query = $query . " and dd.numFacture = :numFacture";
        $query = $query . " and dd.dateFacture = :dateFacture";
        $query = $query . " and dd.quantiteReel = :quantiteReel";
        $query = $query . " and dd.montReel = :montReel";
        $query = $query . " and dd.nature = :nature";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('sousDeclarationCollecteur', $sousDeclarationCollecteur);
        $qb->setParameter('declarationProducteur', $declarationProducteur);
        $qb->setParameter('dechet', $dechet);
        $qb->setParameter('filiere', $filiere);
        $qb->setParameter('traitFiliere', $traitFiliere);
        $qb->setParameter('numFacture', $numFacture);
        $qb->setParameter('quantiteReel', $quantiteReel);
        $qb->setParameter('montReel', $montReel);
        $qb->setParameter('nature', $nature);
        $qb->setParameter('dateFacture', $dateFacture->format('Y-m-d'));
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getDeclarationDetailById($declarationDetail_id) {
        $query = "select dd";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.id = :declarationDetail_id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('declarationDetail_id', $declarationDetail_id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCountStatutBySousDeclarationCollecteurStatut($sousDeclarationCollecteur, $statut) {

        $query = "select count(dd.id)";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.SousDeclarationCollecteur = :sousDeclarationCollecteur";
        $query = $query . " and dd.statut = :statut";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('sousDeclarationCollecteur', $sousDeclarationCollecteur);
        $qb->setParameter('statut', $statut);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getCountStatutByDeclarationProducteurStatut($declarationProducteur, $statut) {

        $query = "select count(dd.id)";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail dd";
        $query = $query . " where dd.DeclarationProducteur = :declarationProducteur";
        $query = $query . " and dd.statut = :statut";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('declarationProducteur', $declarationProducteur);
        $qb->setParameter('statut', $statut);

        //print_r($query);
        return $qb->getSingleScalarResult();
    }

}

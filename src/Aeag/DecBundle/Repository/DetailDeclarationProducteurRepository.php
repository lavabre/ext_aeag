<?php

/**
 * Description of DetailDeclarationProducteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DetailDeclarationProducteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDetailDeclarationProducteurBySousDeclarationCollecteur($sousDeclarationCollecteur) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DetailDeclarationProducteur d";
        $query = $query . " , Aeag\DecBundle\Entity\DeclarationProducteur dp";
        $query = $query . " , Aeag\DecBundle\Entity\Ouvrage o";
        $query = $query . " where d.SousDeclarationCollecteur = " . $sousDeclarationCollecteur;
        $query = $query . " and d.DeclarationProducteur = dp.id";
        $query = $query . " and dp.Producteur = o.id";
        $query = $query . " order by o.numero";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDetailDeclarationProducteurByDeclarationProducteur($declarationProducteur) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DetailDeclarationProducteur d";
        $query = $query . " where d.DeclarationProducteur = " . $declarationProducteur;
        $query = $query . " order by d.id";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDetailDeclarationProducteurById($detailDeclarationProducteur) {
        $query = "select d";
        $query = $query . " from Aeag\DecBundle\Entity\DetailDeclarationProducteur d";
        $query = $query . " where d.id = " . $detailDeclarationProducteur;
        $query = $query . " order by d.id";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

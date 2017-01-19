<?php

/**
 * Description of DechetFiliereRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DechetFiliereRepository
 * @package Aeag\DecBundle\Repository
 */
class DechetFiliereRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDechetFilieres() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\DechetFiliere c";
        $query = $query . " order by c.Dechet.code,c.Filiere.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDechetFiliereByDechet($dechet) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\DechetFiliere c";
        $query = $query . " where c.Dechet = :dechet ";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dechet', $dechet);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDechetFiliereByDechetFiliere($dechet, $filiere) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\DechetFiliere c";
        $query = $query . " where c.Dechet = :dechet";
        $query = $query . " and c.Filiere = :filiere";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dechet', $dechet);
        $qb->setParameter('filiere', $filiere);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getDechetFiliereByDechetFiliereAnnee($dechet, $filiere, $annee) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\DechetFiliere c";
        $query = $query . " where c.Dechet = ':dechet";
        $query = $query . " and c.Filiere = :filiere";
        $query = $query . " and c.annee= :annee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dechet', $dechet);
        $qb->setParameter('filiere', $filiere);
        $qb->setParameter('annee', $annee);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of OuvrageFiliereRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OuvrageFiliereRepository
 * @package Aeag\DecBundle\Repository
 */
class OuvrageFiliereRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getOuvrageFilieres() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\OuvrageFiliere c";
        $query = $query . " order by c.Ouvrage.numero,c.Filiere.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOuvrageFiliereByOuvrage($ouvrage) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\OuvrageFiliere c";
        $query = $query . " where c.Ouvrage = :ouvrage";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvrage', $ouvrage);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOuvrageFiliereByOuvrageFiliere($ouvrage, $filiere, $annee) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\OuvrageFiliere c";
        $query = $query . " where c.Ouvrage = :ouvrage";
        $query = $query . " and c.Filiere = :filiere";
        $query = $query . " and c.annee = :annee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ouvrage', $ouvrage);
        $qb->setParameter('filiere', $filiere);
        $qb->setParameter('annee', $annee);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

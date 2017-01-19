<?php

/**
 * Description of DossierRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DossierRepository
 * @package Aeag\AideBundle\Repository
 */
class DossierRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getDossiers($where) {

        $query = "select a";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier a";
        $query = $query . " where " . $where;
        $query = $query . " order by a.annee, a.ligne, a.dept, a.no_ordre";

        $qb = $this->_em->createQuery($query);
     
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return integer
     */
    public function getNbDossiers($where) {

        $query = "select count(a.id)";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier a";
        $query = $query . " where " . $where;

        $qb = $this->_em->createQuery($query);
      
       // print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return integer
     */
    public function getSumMontantRetenu($where) {

        $query = "select sum(a.montant_retenu)";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier a";
        $query = $query . " where " . $where;

        $qb = $this->_em->createQuery($query);
    
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return integer
     */
    public function getSumMontantAideInterne($where) {

        $query = "select sum(a.montant_aide_interne)";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier a";
        $query = $query . " where " . $where;

        $qb = $this->_em->createQuery($query);
     
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getDossiersByannee($annee) {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier d";
        $query = $query . " where d.annee = :annee";
        $query = $query . " order by d.annee, d.ligne, d.dept, d.no_ordre";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDossiersByanneeLignet($annee, $ligne) {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier d";
        $query = $query . " where d.annee = :annee";
        $query = $query . " and d.ligne = :ligne";
        $query = $query . " order by d.annee, d.ligne, d.dept, d.no_ordre";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('ligne', $ligne);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getDossiersByanneeLigneDept($annee, $ligne, $dept) {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier d";
        $query = $query . " where d.annee = :annee";
        $query = $query . " and d.ligne = :ligne";
        $query = $query . " and d.dept = :dept";
        $query = $query . " order by d.annee, d.ligne, d.dept, d.no_ordre";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('ligne', $ligne);
        $qb->setParameter('dept', $dept);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getDossierBYAnneeLigneDeptNoOrdre($annee, $ligne, $dept, $noOrdre) {

        $query = "select d";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier d";
        $query = $query . " where d.annee = :annee";
        $query = $query . " and d.ligne = :ligne";
        $query = $query . " and d.dept = :dept";
        $query = $query . " and d.no_ordre = :noOrdre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('ligne', $ligne);
        $qb->setParameter('dept', $dept);
        $qb->setParameter('noOrdre', $noOrdre);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getDossierBYAnneeNumero($annee, $numero) {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Dossier c";
        $query = $query . " where c.annee = :annee";
        $query = $query . "and c.numero = :numero";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('numero', $numero);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class FiliereRepository
 * @package Aeag\DecBundle\Repository
 */
class FiliereAideRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getFiliereAideCharges() {
        $query = "select distinct f ";
        $query = $query . " from Aeag\DecBundle\Entity\DeclarationDetail d";
        $query = $query . "    , Aeag\DecBundle\Entity\Filiere f";
        $query = $query . " where f.code = d.Filiere";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getFiliereAides() {
        $query = "select f";
        $query = $query . " from Aeag\DecBundle\Entity\FiliereAide f";
        $query = $query . " order by f.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getFiliereByCode($code) {
        $query = "select f";
        $query = $query . " from Aeag\DecBundle\Entity\FiliereAide f";
        $query = $query . " where f.code =:code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

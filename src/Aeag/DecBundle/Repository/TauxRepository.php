<?php

/**
 * Description of TauxRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class RefParametreRepository
 * @package Aeag\DecBundle\Repository
 */
class TauxRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getTaux() {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Taux c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getTauxByAnneeCode($annee, $code) {
        $query = "select c";
        $query = $query . " from Aeag\DecBundle\Entity\Taux c";
        $query = $query . " where c.annee = :annee";
        $query = $query . " and c.code = :code";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('code', $code);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

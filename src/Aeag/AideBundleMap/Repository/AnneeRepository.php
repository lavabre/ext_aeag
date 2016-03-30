<?php

/**
 * Description of AnneeRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AnneeRepository
 * @package Aeag\AideBundle\Repository
 */
class AnneeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getAnnees() {

        $query = "select a";
        $query = $query . " from Aeag\AideBundle\Entity\Annee a";
        $query = $query . " order by a.annee";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getAnnee($annee) {

        $query = "select a";
        $query = $query . " from Aeag\AideBundle\Entity\Annee a";
        $query = $query . " where a.annee = " . $annee;
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

<?php

/**
 * Description of LigneRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class LigneRepository
 * @package Aeag\AideBundle\Repository
 */
class LigneRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getLignes() {

        $query = "select l";
        $query = $query . " from Aeag\AideBundle\Entity\Ligne l";
        $query = $query . " order by l.ligne";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getLigne($ligne) {

        $query = "select l";
        $query = $query . " from Aeag\AideBundle\Entity\Ligne l";
        $query = $query . " where l.ligne = :ligne";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('ligne', $ligne);

        print_r($query);
        return $qb->getOneOrNullResult();
    }

}

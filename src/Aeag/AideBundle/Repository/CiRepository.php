<?php

/**
 * Description of CiRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CiRepository
 * @package Aeag\AideBundle\Repository
 */
class CiRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCis() {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Ci c";
        $query = $query . " order by c.annee, c.numero";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getCiBYAnnee($annee) {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Ci c";
        $query = $query . " where c.annee = :annee";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getCiBYAnneeNumero($annee, $numero) {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Ci c";
        $query = $query . " where c.annee = :annee";
        $query = $query . "and c.numero = :numero";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('numero', $numero);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

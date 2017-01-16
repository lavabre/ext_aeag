<?php

/**
 * Description of OrganismeRepository
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OrganismeRepository
 * @package Aeag\DieBundle\Repository
 */
class OrganismeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getOrganismes() {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Organisme c";
        $query = $query . " order by c.ordre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getOrganismesByOrganisme($organisme) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Organisme c";
        $query = $query . " where c.organisme = :organisme";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('organisme', $organisme);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getOrganismeById($id) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Organisme c";
        $query = $query . " where c.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}

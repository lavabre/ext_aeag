<?php

/**
 * Description of CollecteurRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CollecteurRepository
 * @package Aeag\DecBundle\Repository
 */
class CollecteurRepository extends EntityRepository {

   
    /**
     * @return array
     */
    public function getCollecteurByUser($user) {
        $query = "select o";
        $query = $query . " from Aeag\DecBundle\Entity\Ouvrage o";
        $query = $query . "   ,Aeag\DecBundle\Entity\OuvrageCorrespondant oc";
        $query = $query . "   ,Aeag\DecBundle\Entity\Correspondant c";
        $query = $query . " where o.id = oc.Ouvrage";
        $query = $query . " and o.type = c.Collecteur'ODEC'";
        $query = $query . " and oc.Correspondant = c.id";
        $query = $query . " and c.user = :user";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('user', $user);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    

}

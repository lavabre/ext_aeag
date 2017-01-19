<?php

/**
 * Description of CorrespndantRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

class InterlocuteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getInterlocuteursByCorrespondant($correspondant) {
        $query = "select i";
        $query = $query . " from Aeag\AeagBundle\Entity\Interlocuteur i";
        $query = $query . " where i.correspondant = :correspondant";
        $query = $query . " order by i.nom";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('correspondant', $correspondant);
        //print_r($query);
        return $qb->getResult();
    }

    public function getInterlocuteurById($id) {

        $query = "select  i";
        $query = $query . " from Aeag\AeagBundle\Entity\Interlocuteur i";
        $query = $query . " where i.id = :id";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}

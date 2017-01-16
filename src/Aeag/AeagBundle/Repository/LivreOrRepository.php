<?php

/**
 * Description of LivreOrtRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LivreOrRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getLivreOrs() {

        $query = "select l";
        $query = $query . " from Aeag\AeagBundle\Entity\LivreOr l";
        $query = $query . " order by l.created";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    public function getLivreOrById($id) {
        $query = "select n from Aeag\AeagBundle\Entity\LivreOr n";
        $query = $query . " where n.id  = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r ($query);
        return $qb->getOneOrNullResult();
    }

    public function getLivreOrByEmettteur($user) {
        $query = "select n from Aeag\AeagBundle\Entity\LivreOr n";
        $query = $query . " where n.Emetteur  = :user";
        $query = $query . " order by n.created";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('user', $user->getId());
        //print_r ($query);
        return $qb->getResult();
    }

}

?>

<?php

/**
 * Description of MessagetRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository {

    public function getMessageById($id) {
        $query = "select n from Aeag\AeagBundle\Entity\Message n";
        $query = $query . " where n.id  = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r ($query);
        return $qb->getOneOrNullResult();
    }

    public function getMessageByRecepteur($user) {
        $query = "select n from Aeag\AeagBundle\Entity\Message n";
        $query = $query . " where n.Recepteur  = :user";
        $query = $query . " order by n.created";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('user', $user->getId());
        //print_r ($query);
        return $qb->getResult();
    }

    public function getMessageByEmettteur($user) {
        $query = "select n from Aeag\AeagBundle\Entity\Message n";
        $query = $query . " where n.Emetteur  = :user";
        $query = $query . " order by n.created";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('user', $user->getId());
        //print_r ($query);
        return $qb->getResult();
    }

}

?>

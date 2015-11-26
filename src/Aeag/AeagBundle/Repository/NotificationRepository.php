<?php

/**
 * Description of NotificationRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{

    public function getNotificationByRecepteur($user)
    {
        $query = "select n from Aeag\AeagBundle\Entity\Notification n";
        $query = $query . " where n.Recepteur  = " . $user->getId();
        $query = $query . " order by n.created";
        $qb = $this->_em->createQuery($query);
        //print_r ($query);
        return $qb->getResult();
    }
    
    public function getNotificationByEmetteur($user)
    {
        $query = "select n from Aeag\AeagBundle\Entity\Notification n";
        $query = $query . " where n.Emetteur  = " . $user->getId();
        $query = $query . " order by n.created";
        $qb = $this->_em->createQuery($query);
        //print_r ($query);
        return $qb->getResult();
    }


}

?>

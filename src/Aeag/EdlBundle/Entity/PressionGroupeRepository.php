<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\EntityRepository;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

/**
 * PressionGroupeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * 
 * 
 */
class PressionGroupeRepository extends EntityRepository {

    public function getPressionGroupe() {


        $query = "select e from Aeag\EdlBundle\Entity\PressionGroupe e";
        $query = $query . " order by e.ordre";
        // return new Response('query  : ' . $query);

        try {
            $r = $this->_em->createQuery($query)
                    ->getResult();
            return $r;
        } catch (Exception $e) {
            return null;
        }
    }

}
<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * EtatMeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * @Secure(roles="ROLE_SUPERVISEUR")
 * 
 */
class MasseEauRepository extends EntityRepository {

    public function getMasseEau($euCd, $groupe) {
        
        
        $query = "select m from Aeag\EdlBundle\Entity\MasseEau m";
        $query = $query . " , Aeag\EdlBundle\Entity\EtatType t";
        $query = $query . " where m.typeMe = t.typeMe and m.euCd = :euCd and t.cdGroupe = :groupe";
        $query = $query . " order by t.ordre";

        return new Response ($query);

        try {
            $query = $qb = $this->_em->createQuery($query)
                    ->setParameter('euCd', $euCd)
                    ->setParameter('groupe', $groupe);

            $r = $query->getResult();
            return reset($r);
        } catch (Exception $e) {
            return null;
        }
    }

}
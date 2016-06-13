<?php

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * EtatDernierePropositionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * 
 */
class EtatDernierePropositionRepository extends EntityRepository {

    public function getDernierePropositionByEucdCdEtat($euCd, $cdEtat) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\EtatDerniereProposition p";
        $query = $query . " where p.euCd = '" . $euCd . "' and p.cdEtat = '" . $cdEtat . "'";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDernierePropositionByEucd($euCd) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\EtatDerniereProposition p";
        $query = $query . " where p.euCd = '" . $euCd . "'";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}



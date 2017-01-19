<?php

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * PressionDernierePropositionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * 
 */
class PressionDernierePropositionRepository extends EntityRepository {

    public function getDernierePropositionByEucdCdPression($euCd, $cdPression) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\PressionDerniereProposition p";
        $query = $query . " where p.euCd = :euCd and p.cdPression = :cdPression";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('euCd', $euCd);
        $qb->setParameter('cdPression', $cdPression);
        //print_r($query);
        return $qb->getResult();
    }

    public function getDernierePropositionByEucd($euCd) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\PressionDerniereProposition p";
        $query = $query . " where p.euCd = ':euCd";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('euCd', $euCd);
        //print_r($query);
        return $qb->getResult();
    }

}

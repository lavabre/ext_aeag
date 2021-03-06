<?php

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PressionMeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PressionMeRepository extends EntityRepository {

    public function getLastPropositionSuperviseur($euCd, $cdPression) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\PressionMeProposed p";
        $query = $query . " where p.euCd = :euCd and p.cdPression = :cdPression and p.role ='expert'";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('euCd', $euCd);
        $qb->setParameter('cdPression', $cdPression);
        //print_r($query);
        return $qb->getResult();

//        $qb = $this->_em->createQueryBuilder();
//
//        try {
//            $query = $qb->select('p') // string 'u' is converted to array internally
//                    ->from('Aeag\EdlBundle\Entity\PressionMeProposed', 'p')
//                    ->where('p.euCd = :euCd and p.cdPression = :cdPression and p.role = :role')
//                    ->setParameter('euCd', $euCd)
//                    ->setParameter('cdPression', $cdPression)
//                    ->setParameter('role', 'expert')
//                    ->orderBy('p.propositionDate', 'DESC')
//                    ->setMaxResults(1)
//                    ->getQuery();
//
//            $r = $query->getOneOrNullResult();
//            return $r;
//        } catch (Exception $e) {
//            return null;
//        }
    }

    public function getLastProposition($euCd, $cdPression) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\PressionMeProposed p";
        $query = $query . " where p.euCd = :euCd and p.cdPression = :cdPression";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('euCd', $euCd);
        $qb->setParameter('cdPression', $cdPression);
        //print_r($query);
        return $qb->getResult();

//        $qb = $this->_em->createQueryBuilder();
//
//        try {
//            $query = $qb->select('p') // string 'u' is converted to array internally
//                    ->from('Aeag\EdlBundle\Entity\PressionMeProposed', 'p')
//                    ->where('p.euCd = :euCd and p.cdPression = :cdPression')
//                    ->setParameter('euCd', $euCd)
//                    ->setParameter('cdPression', $cdPression)
//                    ->orderBy('p.propositionDate', 'DESC')
//                    ->setMaxResults(1)
//                    ->getQuery();
//
//            $r = $query->getOneOrNullResult();
//            return $r;
//        } catch (Exception $e) {
//            return null;
//        }
    }

    public function getPressionMe($code, $cdGroupe) {

        $query = "select e from Aeag\EdlBundle\Entity\MasseEau m";
        $query = $query . " , Aeag\EdlBundle\Entity\PressionType t";
        $query = $query . " , Aeag\EdlBundle\Entity\PressionMe e";
        $query = $query . " where m.euCd = e.euCd and e.cdPression = t.cdPression  and m.euCd = :code and t.cdGroupe = :cdGroupe";
        $query = $query . " order by t.ordre";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        $qb->setParameter('cdGroupe', $cdGroupe);
        //print_r($query);
        return $qb->getResult();
    }

    public function getNbPressionMe($code, $cdGroupe) {

        $query = "select count(e) from Aeag\EdlBundle\Entity\MasseEau m";
        $query = $query . " , Aeag\EdlBundle\Entity\PressionType t";
        $query = $query . " , Aeag\EdlBundle\Entity\PressionMe e";
        $query = $query . " where m.euCd = e.euCd and e.cdPression = t.cdPression  and m.euCd = :code and t.cdGroupe = :cdGroupe";
        //$query = $query . " and e.cdPression != 'RW_ECO_VAL'";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);
        $qb->setParameter('cdGroupe', $cdGroupe);
        //print_r($query);
        return $qb->getSingleScalarResult();
    }

    public function getDerniereProposition($euCd) {

        $query = "select p ";
        $query = $query . " from Aeag\EdlBundle\Entity\PressionMeProposed p";
        $query = $query . " where p.euCd = :euCd";
        $query = $query . " order by p.propositionDate desc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('euCd', $euCd);
        //print_r($query);
        return $qb->getResult();

//        $qb = $this->_em->createQueryBuilder();
//
//        try {
//            $query = $qb->select('p') // string 'u' is converted to array internally
//                    ->from('Aeag\EdlBundle\Entity\PressionMeProposed', 'p')
//                    ->where('p.euCd = :euCd')
//                    ->setParameter('euCd', $euCd)
//                    ->orderBy('p.propositionDate', 'DESC')
//                    ->setMaxResults(1)
//                    ->getQuery();
//
//            $r = $query->getResult();
//            return $r;
//        } catch (Exception $e) {
//            return null;
//        }
    }

}

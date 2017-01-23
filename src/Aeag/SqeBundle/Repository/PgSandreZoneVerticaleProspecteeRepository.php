<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgSandreZoneVerticaleProspecteeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreZoneVerticaleProspectees() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " order by p.codeZone";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreZoneVerticaleProspecteeByCodeZone($codeZone) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.codeZone = :codeZone";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeZone', $codeZone);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgSandreZoneVerticaleProspecteeByMarche($pgProgMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.marche = :pgProgMarche";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgMarche', $pgProgMarche->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreZoneVerticaleProspecteeByTypeMilieu($pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.typeMilieu = :pgProgTypeMilieu";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreZoneVerticaleProspecteeByTitulaire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.titulaire = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

}

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
        $query = $query . " where p.codeZone = '" . $codeZone . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgSandreZoneVerticaleProspecteeByMarche($PgProgMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.marche = " . $PgProgMarche->getid();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }


    public function getPgSandreZoneVerticaleProspecteeByTypeMilieu($PgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.typeMilieu = " . $PgProgTypeMilieu->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgSandreZoneVerticaleProspecteeByTitulaire($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee p";
        $query = $query . " where p.titulaire = " . $PgRefCorresPresta->getAdrCorId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}

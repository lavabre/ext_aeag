<?php

/**
 * Description of PgProgLotRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLots() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " order by p.nomLot";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgLotById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotByNomLot($nomLot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.nomLot = :nomLot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('nomLot', nomLot);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgLotByMarche($pgProgMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.marche = :pgProgMarche";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgMarche', $pgProgMarche->getid());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotByTypeMilieu($pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.codeMilieu = :pgProgTypeMilieu";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotByTitulaire($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.titulaire = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPrestat', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotByMarcheZoneGeoRefTypeMilieu($marche, $zoneGeoRef, $typeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLot p";
        $query = $query . " where p.id = p.id ";
        if ($marche) {
            $query = $query . " and p.marche = :marche";
        }
        if ($zoneGeoRef) {
            $query = $query . " and p.zgeoRef = :zoneGeoRef";
        }
        if ($typeMilieu) {
            $query = $query . " and p.codeeMilieu = :typeMilieu";
        }
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('marche', $marche);
        $qb->setParameter('zoneGeoRef', $zoneGeoRef);
        $qb->setParameter('typeMilieu', $typeMilieu);
        //print_r($query);
        return $qb->getResult();
    }

}

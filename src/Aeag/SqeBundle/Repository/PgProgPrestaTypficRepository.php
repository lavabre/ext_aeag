<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgPrestaTypficRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgPrestaTypficRepository extends EntityRepository {

    public function getPgProgPrestaTypficByPrestataire($prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.prestataire = :prestataire";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.codeMilieu = :pgProgTypeMilieu";
        $query = $query . " and p.prestataire = :prestataire";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $prestataire, $formatFic) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPrestaTypfic p";
        $query = $query . " where p.codeMilieu = :pgProgTypeMilieu";
        $query = $query . " and p.prestataire = :prestataire";
        $query = $query . " and upper(p.formatFic) like :formatFic";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgProgTypeMilieu', $pgProgTypeMilieu->getCodeMilieu());
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        $qb->setParameter('formatFic', '%' . strtoupper($formatFic) . '%');
        //   print_r($query . ' <br/> pgProgTypeMilieu :  ' . $pgProgTypeMilieu->getCodeMilieu() . ' prestataire :  ' . $prestataire->getAdrCorId() . ' formatFic : ' . $formatFic);
        return $qb->getOneOrNullResult();
    }

}

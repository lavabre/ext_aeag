<?php
namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgTmpValidEdilaboRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgTmpValidEdilaboRepository extends EntityRepository {
    
    public function getDaiByRai($reponseId) {
        $query = "select dmd";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps, Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where rps.demandeId = dmd.demandeId";
        $query .= " and rps.codeDemande = dmd.codeDemande";
        $query .= " and rps.codePrelevement = dmd.codePrelevement";
        $query .= " and rps.codeStation = dmd.codeStation";
        $query .= " and rps.codeSupport = dmd.codeSupport";
        $query .= " and rps.preleveur = dmd.preleveur"; //
        $query .= " and rps.codeParametre = dmd.codeParametre";
        $query .= " and rps.codeFraction = dmd.codeFraction";
        $query .= " and rps.codeUnite = dmd.codeUnite";
        $query .= " and rps.labo = dmd.labo";
        $query .= " and rps.id = :reponse";
        $query .= " and dmd.fichierRpsId IS NULL";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('reponse', $reponseId);
        return $qb->getResult();
    }
    
    public function getCodeDemande($demandeId, $reponseId = null) {
        $query = "select distinct p.codeDemande";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        
        return $qb->getOneOrNullResult();
    }
    
    public function getCodeIntervenant($demande, $reponse = null) {
        
    }
    
    public function getDiffCodeDemande($demandeId, $reponseId) {
        $query = "select distinct dmd.codeDemande";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codeDemande NOT IN";
        $query .= " (select distinct rps.codeDemande";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        return $qb->getResult();
    }
    
    public function getDiffCodePrelevement($demandeId, $reponseId) {
        $query = "select distinct dmd.codePrelevement";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement NOT IN";
        $query .= " (select distinct rps.codePrelevement";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        return $qb->getResult();
    }
    
    public function getCodePrelevement($demandeId, $reponseId = null) {
        $query = "select distinct p.codePrelevement";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }
        $query .= " order by p.codePrelevement ASC";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        return $qb->getResult();
    }
    
    public function getDatePrelevement($codePrelevement, $demandeId, $reponseId = null) {
        $query = "select distinct p.datePrel";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }
        $query .= " and p.codePrelevement = :codePrelevement";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getOneOrNullResult();
    }
    
    public function getStationsByCodePrelevement($codePrelevement, $demandeId, $reponseId = null) {
        $query = "select distinct p.codeStation";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }
        $query .= " and p.codePrelevement = :codePrevelement";
        $query .= " order by p.codePrelevement ASC";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        $qb->setParameter('codePrelevement', $codePrelevement);
        
        return $qb->getResult();
    }
    
}

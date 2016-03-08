<?php
namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgTmpValidEdilaboRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgTmpValidEdilaboRepository extends EntityRepository {
    
    public function getDiffCodeDemande($demandeId, $reponseId) {
        $query = " select distinct rps.codeDemande";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codeDemande NOT IN";
        $query .= " (select distinct dmd.codeDemande";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        return $qb->getResult();
    }
    
    public function getDiffCodePrelevementAdd($demandeId, $reponseId) {
        $query = " select distinct rps.codePrelevement";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement NOT IN";
        $query .= " (select distinct dmd.codePrelevement";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL)";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        return $qb->getResult();
    }
    
    public function getDiffCodePrelevementMissing($demandeId, $reponseId) {
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
    
    public function getDiffLabo($codePrelevement, $demandeId, $reponseId) {
        $query = " select distinct rps.labo";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement = :codePrelevement";
        $query .= " and rps.labo NOT IN";
        $query .= " (select distinct dmd.labo";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement = :codePrelevement)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    public function getDiffPreleveur($codePrelevement, $demandeId, $reponseId) {
        $query = " select distinct rps.preleveur";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement = :codePrelevement";
        $query .= " and rps.preleveur NOT IN";
        $query .= " (select distinct dmd.preleveur";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement = :codePrelevement)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    public function getDiffCodeStation($codePrelevement, $demandeId, $reponseId) {
        $query = " select distinct rps.codeStation";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement = :codePrelevement";
        $query .= " and rps.codeStation NOT IN";
        $query .= " (select distinct dmd.codeStation";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement = :codePrelevement)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    public function getMesures($codePrelevement, $demandeId, $reponseId = null) {
        $query = "select p.codeFraction, p.codeUnite, p.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }
        $query .= " and p.codePrelevement = :codePrelevement";
        $query .= " order by p.codeParametre ASC";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    public function getMesureByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement) {
        $query = "select p.resM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $result = $qb->getOneOrNullResult();
        
        if (!is_null($result)) {
            $result = $result['resM'];
        }
        return $result;
    }
    
    public function getLqByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement) {
        $query = "select p.lqM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $result = $qb->getOneOrNullResult();
        
        if (!is_null($result)) {
            $result = $result['lqM'];
        }
        return $result;
    }
    
    public function getCodeRqByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement) {
        $query = "select p.codeRqM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $result = $qb->getOneOrNullResult();
        
        if (!is_null($result)) {
            $result = $result['codeRqM'];
        }
        return $result;
    }
    
    public function getDiffCodeParametreAdd($codePrelevement, $demandeId, $reponseId) {
         $query = " select rps.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement = :codePrelevement";
        $query .= " and rps.codeParametre NOT IN";
        $query .= " (select distinct dmd.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement = :codePrelevement)";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    public function getDiffCodeParametreMissing($codePrelevement, $demandeId, $reponseId) {
        
        $query = "select distinct dmd.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo dmd";
        $query .= " where dmd.demandeId = :demande";
        $query .= " and dmd.fichierRpsId IS NULL";
        $query .= " and dmd.codePrelevement = :codePrelevement";
        $query .= " and dmd.codeParametre NOT IN";
        $query .= " (select rps.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo rps";
        $query .= " where rps.demandeId = :demande";
        $query .= " and rps.fichierRpsId = :reponse";
        $query .= " and rps.codePrelevement = :codePrelevement)";
        
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        return $qb->getResult();
    }
    
    
    
}

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

        $query = "select dmd.*, rps.* from";
        $query .= " (select distinct code_parametre, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id is null) dmd";
        $query .= " left join (select distinct code_parametre, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id is not null) rps";
        $query .= " on  dmd.code_parametre = rps.code_parametre and ((dmd.code_fraction is null and rps.code_fraction is null) or (dmd.code_fraction = rps.code_fraction))";
        $query .= " where rps.code_parametre is null";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('demande', $demandeId);
        $stmt->execute();
        return $stmt->fetchAll();
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

        $result = $qb->getResult();
        if (count($result) > 1) {
            return -1;
        } else if (count($result) == 0) {
            return null;
        }
        return $result[0];
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
        $query = "select p.codeFraction, p.codeUnite, p.codeParametre, p.lqM";
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

    public function getMesuresByCodeParametre($codeParametre, $codePrelevement, $demandeId, $reponseId = null, $codeFraction = null) {
        $query = "select p.codeFraction, p.codeUnite, p.codeParametre, p.lqM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        if (!is_null($reponseId)) {
            $query .= " and p.fichierRpsId = :reponse";
        } else {
            $query .= " and p.fichierRpsId IS NULL";
        }

        if (!is_null($codeFraction)) {
            $query .= " and p.codeFraction = :codeFraction";
        }

        $query .= " and p.codePrelevement = :codePrelevement";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " order by p.codeParametre ASC";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        if (!is_null($reponseId)) {
            $qb->setParameter('reponse', $reponseId);
        }
        $qb->setParameter('codePrelevement', $codePrelevement);
        $qb->setParameter('codeParametre', $codeParametre);
        if (!is_null($codeFraction)) {
            $qb->setParameter('codeFraction', $codeFraction);
        }

        $result = $qb->getResult();
        if (count($result) > 1) {
            return -1;
        } else if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }

    public function getMesureByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction = null) {
        $query = "select p.resM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        if (!is_null($codeFraction)) {
            $query .= " and p.codeFraction = :codeFraction";
        }

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        if (!is_null($codeFraction)) {
            $qb->setParameter('codeFraction', $codeFraction);
        }

        $result = $qb->getResult();
        if (count($result) > 1) {
            return -999;
        } else if (count($result) == 0) {
            return null;
        }
        return $result[0]['resM'];
    }

    public function getAllMesureByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction = null) {
        $query = "select p.resM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        if (!is_null($codeFraction)) {
            $query .= " and p.codeFraction = :codeFraction";
        }

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        if (!is_null($codeFraction)) {
            $qb->setParameter('codeFraction', $codeFraction);
        }

        return $qb->getResult();
    }

    public function getMesureByCodeUnite($codeUnite, $demandeId, $reponseId, $codePrelevement, $excludeCodeParam = null) {
        $query = "select p.resM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeUnite = :codeUnite";
        $query .= " and p.codePrelevement = :codePrelevement";
        if (!is_null($excludeCodeParam)) {
            $query .= " and p.codeParametre <> :codeParametre";
        }
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeUnite', $codeUnite);
        $qb->setParameter('codePrelevement', $codePrelevement);
        if (!is_null($excludeCodeParam)) {
            $qb->setParameter('codeParametre', $excludeCodeParam);
        }
        $results = $qb->getResult();

        if (count($results) > 0) {
            foreach ($results as &$result) {
                $result = $result['resM'];
            }
        }
        return $results;
    }

    public function getCodesParametres($demandeId, $reponseId, $codePrelevement) {
        $query = "select distinct p.codeParametre";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codePrelevement = :codePrelevement";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $results = $qb->getResult();

        if (count($results) > 0) {
            foreach ($results as &$result) {
                $result = $result['codeParametre'];
            }
        }
        return $results;
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
        try {
            $result = $qb->getOneOrNullResult();
        } catch (Doctrine\ORM\NonUniqueResultException $ex) {
            return new self('Le code paramètre ' . $codeParametre . ' possède plusieurs lq (' . $codePrelevement . ')');
        }

        if (!is_null($result)) {
            $result = $result['lqM'];
        }
        return $result;
    }

    public function getCodeRqByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction = null) {
        $query = "select p.codeRqM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codeParametre = :codeParametre";
        $query .= " and p.codePrelevement = :codePrelevement";
        if (!is_null($codeFraction)) {
            $query .= " and p.codeFraction = :codeFraction";
        }
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codeParametre', $codeParametre);
        $qb->setParameter('codePrelevement', $codePrelevement);
        if (!is_null($codeFraction)) {
            $qb->setParameter('codeFraction', $codeFraction);
        }

        $result = $qb->getResult();
        if (count($result) > 1) {
            return -1;
        } else if (count($result) == 0) {
            return null;
        }
        return $result[0]['codeRqM'];
    }

    public function getCodeRqValideByCodePrelevement($demandeId, $reponseId, $codePrelevement) {
        $query = "select distinct p.codeRqM";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codePrelevement = :codePrelevement";
        $query .= " and p.codeRqM IS NOT NULL";
        $query .= " and p.inSitu <> '0'";
        $query .= " and p.codeRqM <> '0'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $result = $qb->getResult();

        return $result;
    }

    public function getDiffCodeParametreAdd($codePrelevement, $demandeId, $reponseId) {
        //TODO A modifier comme le Missing
        $query = "select dmd.*, rps.* from";
        $query .= " (select distinct code_parametre as codeparamdmd, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id = :reponse and code_prelevement = :codePrelevement) dmd";
        $query .= " left join (select distinct code_parametre as codeparamrps, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id is null and code_prelevement = :codePrelevement) rps";
        $query .= " on  codeparamdmd = codeparamrps";
        $query .= " where codeparamrps is null";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('demande', $demandeId);
        $stmt->bindValue('reponse', $reponseId);
        $stmt->bindValue('codePrelevement', $codePrelevement);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $return = array();
        foreach ($results as $result) {
            if (!is_null($result['codeparamdmd'])) {
                $return[] = $result['codeparamdmd'];
            }
        }

        return $return;
    }

    public function getDiffCodeParametreMissing($codePrelevement, $demandeId, $reponseId) {

        $query = "select dmd.*, rps.* from";
        $query .= " (select distinct code_parametre as codeparamdmd, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id is null and code_prelevement = :codePrelevement) dmd";
        $query .= " left join (select distinct code_parametre as codeparamrps, code_fraction from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id = :reponse and code_prelevement = :codePrelevement) rps";
        $query .= " on  codeparamdmd = codeparamrps";
        $query .= " where codeparamrps is null";
        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('demande', $demandeId);
        $stmt->bindValue('reponse', $reponseId);
        $stmt->bindValue('codePrelevement', $codePrelevement);

        $stmt->execute();

        $results = $stmt->fetchAll();

        $return = array();
        foreach ($results as $result) {
            if (!is_null($result['codeparamdmd'])) {
                $return[] = $result['codeparamdmd'];
            }
        }

        return $return;
    }

    public function getDiffCodeFraction($codePrelevement, $demandeId, $reponseId) {

        $query = "select dmd.*, rps.* from";
        $query .= " (select distinct code_parametre as codeparamdmd, code_fraction as codefractiondmd  from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id is null and code_prelevement = :codePrelevement) dmd";
        $query .= " left join (select distinct code_parametre as codeparamrps, code_fraction as codefractionrps  from pg_tmp_valid_edilabo";
        $query .= " where demande_id = :demande and fichier_rps_id = :reponse and code_prelevement = :codePrelevement) rps";
        $query .= " on  codeparamdmd = codeparamrps and codefractiondmd  = codefractionrps ";
        $query .= " where codeparamrps is null and codefractiondmd is not null ";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue('demande', $demandeId);
        $stmt->bindValue('reponse', $reponseId);
        $stmt->bindValue('codePrelevement', $codePrelevement);

        $stmt->execute();

        $results = $stmt->fetchAll();

        $return = array();
        foreach ($results as $result) {
            if (!is_null($result['codeparamdmd'])) {
                $return[] = $result['codeparamdmd'];
            }
        }

        return $return;
    }

    public function getCodesMethodes($codePrelevement, $demandeId, $reponseId) {
        $query = "select distinct p.methAna, p.methPrel";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId = :reponse";
        $query .= " and p.codePrelevement = :codePrelevement";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);
        $qb->setParameter('reponse', $reponseId);
        $qb->setParameter('codePrelevement', $codePrelevement);
        $result = $qb->getResult();

        return $result;
    }

    public function getPgTmpValidEdilaboDmd($demandeId) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId IS NULL";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);

        $result = $qb->getResult();

        return $result;
    }

    public function getPgTmpValidEdilaboRps($demandeId) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgTmpValidEdilabo p";
        $query .= " where p.demandeId = :demande";
        $query .= " and p.fichierRpsId IS NOT NULL";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demandeId);

        $result = $qb->getResult();

        return $result;
    }

}

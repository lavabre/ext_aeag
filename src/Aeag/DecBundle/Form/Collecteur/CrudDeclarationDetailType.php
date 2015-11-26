<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Aeag\DecBundle\Repository\NafRepository;
use Aeag\DecBundle\Repository\DechetRepository;
use Aeag\DecBundle\Repository\FiliereRepository;

class CrudDeclarationDetailType extends AbstractType {

    public function __construct($parametres) {
        $this->idCrud = $parametres[0];
        $this->idCollecteur = $parametres[1];
        $this->idCTT = $parametres[2];
        $this->idCT = $parametres[3];
        $this->idCD = $parametres[4];
        $this->idProducteur = $parametres[5];
        $this->producteurs = $parametres[6];
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $crud = $this->idCrud;
        $idCollecteur = $this->idCollecteur;
        $idCTT = $this->idCTT;
        $idCT = $this->idCT;
        $idCD = $this->idCD;
        $producteurs = $this->producteurs;
        if ($crud == 'C') {
            $builder
                    ->add('Producteur', 'text', array('label' => 'Producteur', 'required' => false))
                    ->add('Naf', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Naf',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Activité (code NAF)',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                        'query_builder' => function(NafRepository $er) {
                    return $er->createQueryBuilder('a')
                            ->where('a.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('a.code', 'asc');
                },
                    ))
                    ->add('numFacture', 'text', array('label' => 'N° facture', 'required' => true))
                    ->add('dateFacture', 'date', array('label' => 'N° facture',
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'required' => true))
                    ->add('Dechet', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Dechet',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code nomemclature',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                       'query_builder' => function(DechetRepository $er) {
                    return $er->createQueryBuilder('a')
                            ->where('a.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('a.code', 'asc');
                },
                    ))
                    ->add('nature', 'text', array('label' => 'nature', 'required' => true))
                    ->add('traitFiliere', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Filiere',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code D/R',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                        'query_builder' => function(FiliereRepository $er) {
                    return $er->createQueryBuilder('d')
                            ->where('(d.code like \'D%\' or d.code like \'R%\') and d.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('d.code', 'ASC');
                },
                    ))
                    ->add('CentreTraitement', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Centre de traitement',
                        'empty_value' => '',
                        'choice_label' => 'LibelleNumero',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                            ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'CTT')
                            ->setParameter('aidable', 'N');
                    return $qb->orderBy('d.libelle', 'ASC');
                },
                    ))
                    ->add('quantiteReel', 'text', array('label' => 'Quantité pesée (kg)', 'required' => true))
                    ->add('quantiteRet', 'text', array('label' => 'Quantité retenue (kg)', 'required' => false, 'read_only' => true))
                    ->add('FiliereAide', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\FiliereAide',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code de conditionnement',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d');
                    return $qb->orderBy('d.code', 'ASC');
                },
                    ))
                    ->add('coutFacture', 'text', array('label' => 'Coût facturé (€/kg)',
                        'required' => true))
                    ->add('montAide', 'text', array('label' => 'Montant de l\'aide', 'required' => true))
                    ->add('montRet', 'text', array('label' => 'Montant retenu de l\'aide', 'required' => false, 'read_only' => true))
                    ->add('CentreDepot', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => false,
                        'label' => 'Centre d\'entreposage',
                        'empty_value' => '',
                        'choice_label' => 'LibelleNumero',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                            ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'ODEC')
                            ->setParameter('aidable', 'N');
                    return $qb->orderBy('d.libelle', 'ASC');
                },
                    ))
                    ->add('CentreTransit', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => false,
                        'read_only' => true,
                        'label' => 'Siret du centre de transit',
                        'empty_value' => '',
                        'choice_label' => 'LibelleSiretCpVille',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                             ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'CT')
                            ->setParameter('aidable', 'N');
                    return $qb->orderBy('d.libelle, d.siret', 'ASC');
                },
                    ))
            ;
        } else {
            $idProducteur = $this->idProducteur;
            $builder
                    ->add('Producteur', 'text', array('label' => 'Producteur', 'required' => false))
                    ->add('Naf', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Naf',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Activité (code NAF)',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                       'query_builder' => function(NafRepository $er) {
                    return $er->createQueryBuilder('a')
                            ->where('a.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('a.code', 'asc');
                },
                    ))
                    ->add('numFacture', 'text', array('label' => 'N° facture', 'required' => true))
                    ->add('dateFacture', 'date', array('label' => 'N° facture',
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'required' => true))
                    ->add('Dechet', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Dechet',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code nomemclature',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                         'query_builder' => function(DechetRepository $er) {
                    return $er->createQueryBuilder('a')
                            ->where('a.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('a.code', 'asc');
                },
                    ))
                    ->add('nature', 'text', array('label' => 'nature', 'required' => true))
                    ->add('traitFiliere', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\Filiere',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code D/R',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                        'query_builder' => function(FiliereRepository $er) {
                    return $er->createQueryBuilder('d')
                            ->where('(d.code like \'D%\' or d.code like \'R%\') and d.aidable != :aidable')
                            ->setParameter('aidable', 'N')
                            ->orderBy('d.code', 'ASC');
                },
                    ))
                    ->add('CentreTraitement', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Centre de traitement',
                        'empty_value' => '',
                        'choice_label' => 'LibelleNumero',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                            ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'CTT')
                            ->setParameter('aidable', 'N');
                    return $qb->orderBy('d.libelle', 'ASC');
                },
                    ))
                    ->add('quantiteReel', 'text', array('label' => 'Quantité pesée (kg)', 'required' => true))
                    ->add('quantiteRet', 'text', array('label' => 'Quantité retenue (kg)', 'required' => false, 'read_only' => true))
                    ->add('FiliereAide', 'entity', array(
                        'class' => 'Aeag\DecBundle\Entity\FiliereAide',
                        'multiple' => false,
                        'required' => true,
                        'label' => 'Code de conditionnement',
                        'empty_value' => '',
                        'choice_label' => 'CodeLibelle',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d');
                    return $qb->orderBy('d.code', 'ASC');
                },
                    ))
                    ->add('coutFacture', 'text', array('label' => 'Coût facturé (€/kg)', 'required' => true))
                    ->add('montAide', 'text', array('label' => 'Montant de l\'aide', 'required' => true))
                    ->add('montRet', 'text', array('label' => 'Montant retenu de l\'aide', 'required' => false, 'read_only' => true))
                    ->add('CentreDepot', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => false,
                        'label' => 'Centre d\'entreposage',
                        'empty_value' => '',
                        'choice_label' => 'LibelleNumero',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                            ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'ODEC')
                            ->setParameter('aidable', 'N');;
                    return $qb->orderBy('d.libelle', 'ASC');
                },
                    ))
                    ->add('CentreTransit', 'entity', array(
                        'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                        'multiple' => false,
                        'required' => false,
                        'read_only' => false,
                        'label' => 'Siret du centre de transit',
                        'empty_value' => '',
                        'choice_label' => 'LibelleSiretCpVille',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('d')
                            ->where('d.type = :type and d.dec != :aidable')
                            ->setParameter('type', 'CT')
                            ->setParameter('aidable', 'N');;
                    return $qb->orderBy('d.libelle, d.siret', 'ASC');
                },
                    ))
            ;
        }
    }

    public function getName() {
        return 'crudDeclarationDetail';
    }

}

<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FormReadDeclarationDetailType extends AbstractType {

    public function __construct($parametres) {
        $this->idCrud = $parametres[0];
        $this->idCollecteur = $parametres[1];
        $this->idProducteur = $parametres[2];
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $crud = $this->idCrud;
        $idProducteur = $this->idProducteur;
        $builder
                ->add('Producteur', 'text', array('label' => 'Producteur', 'required' => false))
                ->add('Naf', 'entity', array(
                    'class' => 'Aeag\DecBundle\Entity\Naf',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Activité (code NAF)',
                    'empty_value' => '',
                    'choice_label' => 'CodeLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d');
                return $qb->orderBy('d.code', 'ASC');
                ;
            },
                ))
                ->add('numFacture', 'text', array('label' => 'N° facture', 'required' => false, 'read_only' => true))
                  ->add('dateFacture', 'date', array('label' => 'N° facture',
                         'widget' => 'single_text',
                         'format' => 'dd/MM/yyyy',
                         'required' => false,
                        'read_only' => true,))
                
                ->add('Dechet', 'entity', array(
                    'class' => 'Aeag\DecBundle\Entity\Dechet',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Code nomemclature',
                    'empty_value' => '',
                    'choice_label' => 'CodeLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d');
                return $qb->orderBy('d.code', 'ASC');
                ;
            },
                ))
                ->add('nature', 'text', array('label' => 'nature', 'required' => true, 'read_only' => true))
                ->add('traitFiliere', 'entity', array(
                    'class' => 'Aeag\DecBundle\Entity\Filiere',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Code D/R',
                    'empty_value' => '',
                    'choice_label' => 'CodeLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d')
                        ->where('d.code like \'D%\' or d.code like \'R%\'');
                return $qb->orderBy('d.code', 'ASC');
                ;
            },
                ))
                ->add('CentreTraitement', 'entity', array(
                    'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Centre de traitement',
                    'empty_value' => '',
                    'choice_label' => 'NumeroLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d')
                        ->where('d.type = :type and d.dec = \'O\'')
                        ->setParameter('type', 'CTT');
                return $qb->orderBy('d.numero', 'ASC');
                ;
            },
                ))
                ->add('quantiteReel', 'text', array('label' => 'Quantité pesée (kg)', 'required' => false, 'read_only' => true))
                ->add('quantiteRet', 'text', array('label' => 'Quantité retenue (kg)', 'required' => false, 'read_only' => true))
                ->add('FiliereAide', 'entity', array(
                    'class' => 'Aeag\DecBundle\Entity\FiliereAide',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Code de conditionnement',
                    'empty_value' => '',
                    'choice_label' => 'CodeLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d');
                return $qb->orderBy('d.code', 'ASC');
                ;
            },
                ))
                ->add('coutFacture', 'text', array('label' => 'Coût facturé (€/kg)',
                    'required' => false,
                    'read_only' => true))
                ->add('montAide', 'text', array('label' => 'Montant de l\'aide', 'required' => false, 'read_only' => true))
                ->add('montRet', 'text', array('label' => 'Montant retenu de l\'aide', 'required' => false, 'read_only' => true))
                ->add('CentreDepot', 'entity', array(
                    'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Centre d\'entreposage',
                    'empty_value' => '',
                    'choice_label' => 'NumeroLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d')
                        ->where('d.type = :type and d.dec = \'O\'')
                        ->setParameter('type', 'ODEC');
                return $qb->orderBy('d.numero', 'ASC');
                ;
            },
                ))
                ->add('CentreTransit', 'entity', array(
                    'class' => 'Aeag\AeagBundle\Entity\Ouvrage',
                    'multiple' => false,
                    'required' => false,
                    'read_only' => true,
                    'label' => 'Siret du centre de transit',
                    'empty_value' => '',
                    'choice_label' => 'SiretLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                $qb = $er->createQueryBuilder('d')
                        ->where('d.type = :type and d.dec = \'O\'')
                        ->setParameter('type', 'CT');
                return $qb->orderBy('d.numero', 'ASC');
                ;
            },
                ))
        ;
    }

    public function getName() {
        return 'crudDeclarationDetail';
    }

}

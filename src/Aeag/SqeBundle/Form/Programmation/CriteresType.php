<?php

namespace Aeag\SqeBundle\Form\Programmation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Aeag\SqeBundle\Repository\PgProgWebusersRepository;
use Aeag\SqeBundle\Repository\PgProgMarcheRepository;
use Aeag\SqeBundle\Repository\PgRefCorresPrestaRepository;
use Aeag\SqeBundle\Repository\PgProgZoneGeoRefRepository;
use Aeag\SqeBundle\Repository\PgProgTypeMilieuRepository;
use Aeag\SqeBundle\Repository\PgProgLotRepository;

class CriteresType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                 ->add('annee', 'text', array('label' => 'Annee', 'required' => false))
                ->add('webuser', 'text', array('required' => false,'label' => 'Utilisateurs' ))
                ->add('marche', 'text', array('required' => false,'label' => 'Marché'       ))
                ->add('titulaire', 'text', array('required' => false,'label' => 'Prestataire'    ))
                ->add('zoneGeoRef', 'text', array('required' => false,'label' => 'Zone géographique' ))
                ->add('catMilieu', 'text', array('required' => false,'label' => 'catégorie de milieu'      ))
                ->add('typeMilieu', 'text', array('required' => false,'label' => 'Type de milieu'      ))
                ->add('lot', 'text', array('required' => false,'label' => 'Lot'          ))
                ->add('phase', 'text', array('required' => false,'label' =>'Phase'          ))
      ;
    }

    public function getName() {
        return 'criteres';
    }

}

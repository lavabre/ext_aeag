<?php

namespace Aeag\FrdBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExporterFraisDeplacementType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('exporter', 'choice', array('choices' => array('O' => 'Oui', 'N' => 'Non'),
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true))

        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return '';
    }

}

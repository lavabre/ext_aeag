<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajDecType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('dec', 'choice', array('choices' => array('O' => 'Oui', 'N' => 'Non'),
                    'required' => true,
                    'empty_value' => false))
        ;
    }

    public function getName() {
        return 'majDec';
    }

}

<?php

namespace Aeag\SqeBundle\Twig;

class ClassTwigExtension extends \Twig_Extension {

    public function getFunctions() {
        return array(
            'class' => new \Twig_SimpleFunction('class', array($this, 'getClass'))
        );
    }

    public function getName() {
        return 'class_twig_extension';
    }

    public function getClass($object) {
        return (new \ReflectionClass($object))->getShortName();
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('strpad', array($this, 'strpadFilter')),
        );
    }

    public function strpadFilter($number, $pad_length, $pad_string) {
        return str_pad($number, $pad_length, $pad_string, STR_PAD_LEFT);
    }

}

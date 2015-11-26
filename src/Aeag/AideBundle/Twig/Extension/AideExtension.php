<?php

namespace Aeag\AideBundle\Twig\Extension;

class AideExtension extends \Twig_Extension {

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'aide';
    }

    public function getFunctions() {
        return array(
            'paragraph' => new \Twig_Function_Method($this, 'paragraph', array(
                'is_safe' => array('html')
            )),
        );
    }

    public function getFilters() {
        return array(
            'truncate' => new \Twig_Filter_Method($this, 'truncate'),
            'number' => new \Twig_Filter_Method($this,'formatNumber'),
            'string' => new \Twig_Filter_Method($this, 'formatString'),
        );
    }

    public function paragraph($text, array $options = array()) {
        $css = (isset($options['class'])) ? sprintf('class="%s"', $options['class']) : '';

        $paths = array("/(\r\n|\r)/", "/\n{2,}/");
        $replacements = array("\n", "<p><p$css>");

        $text = preg_replace($paths, $replacements, $text);
        $text = str_replace("\n", "\n<br />", $text);

        return '<p' . $css . '>' . $text . '</p>';
    }

    public function truncate($text, $max = 30) {
        if (mb_strlen($text) > $max) {
            $text = mb_substr($text, 0, $max);
            $text = mb_substr($text, 0, mb_strpos($text, ' ')) . '...';
        }
        return $text;
    }

    function formatNumber($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',') {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }

    function formatString($number = 0) {
        if (is_integer($number)) {
            $text = strval($number);
            return $text;
        }
        return $number;
    }

}


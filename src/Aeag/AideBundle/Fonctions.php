<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Fonctions
 *
 * @author lavabre
 * 
 * 
 */

namespace Aeag\AideBundle;

class Fonctions {

    public function tri_dossiers($a, $b) {
        $al = strtolower($a->getLigne()->getLigne() . $a->getDept()->getDept() . $a->getNo_ordre());
        $bl = strtolower($b->getLigne()->getLigne() . $b->getDept()->getDept() . $b->getNo_ordre());
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

}

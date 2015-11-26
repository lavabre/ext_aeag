<?php

namespace Aeag\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AeagUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}

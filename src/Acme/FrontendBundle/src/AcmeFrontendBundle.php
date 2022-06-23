<?php

namespace App\Acme\FrontendBundle\src;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeFrontendBundle extends Bundle
{

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
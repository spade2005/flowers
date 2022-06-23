<?php

namespace App\Acme\BackendBundle\src;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeBackendBundle extends Bundle
{

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
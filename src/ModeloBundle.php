<?php

namespace Choosit\ModeloBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ModeloBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

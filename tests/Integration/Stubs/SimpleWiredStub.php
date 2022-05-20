<?php

namespace Choosit\ModeloBundle\Tests\Integration\Stubs;

use Choosit\ModeloBundle\Service\ModeloClientInterface;

class SimpleWiredStub
{
    /**
     * @var ModeloClientInterface
     */
    private $modeloClient;

    public function __construct(ModeloClientInterface $modeloClient)
    {
        $this->modeloClient = $modeloClient;
    }

    public function getModeloClient(): ModeloClientInterface
    {
        return $this->modeloClient;
    }
}

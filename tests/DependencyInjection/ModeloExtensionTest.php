<?php

namespace Choosit\ModeloBundle\Tests\DependencyInjection;

use Choosit\ModeloBundle\DependencyInjection\ModeloExtension;
use Choosit\ModeloBundle\Exception\AuthKeyMissingException;
use Choosit\ModeloBundle\ModeloBundle;
use Choosit\ModeloBundle\Service\ModeloClientInterface;
use Choosit\ModeloBundle\Tests\Integration\Stubs\Kernel;
use Choosit\ModeloBundle\Tests\Integration\Stubs\SimpleWiredStub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ModeloExtensionTest extends TestCase
{
    public function testLoadBaseConfiguration(): void
    {
        $container = $this->createContainer([
            'framework' => [
                'secret' => 'testing',
            ],
            'modelo' => [
                'base_uri' => 'https://doc.staging.modelo.fr',
                'auth' => [
                    'agency_code' => 'test',
                    'private_key' => 'test',
                ],
            ],
        ]);

        $container
            ->register('autowired', SimpleWiredStub::class)
            ->setPublic(true)
            ->setAutowired(true);

        $container->compile();

        $service = $container->get('autowired');
        $this->assertInstanceOf(ModeloClientInterface::class, $service->getModeloClient());
        $this->assertNotNull($service->getModeloClient()->getAuthKey());
    }

    public function testLoadEmptyConfiguration(): void
    {
        $container = $this->createContainer([
            'framework' => [
                'secret' => 'testing',
            ],
            'modelo' => [],
        ]);

        $container
            ->register('autowired', SimpleWiredStub::class)
            ->setPublic(true)
            ->setAutowired(true);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('base_uri parameter must be defined.');

        $container->compile();
    }

    public function testLoadMinimalConfiguration(): void
    {
        $container = $this->createContainer([
            'framework' => [
                'secret' => 'testing',
            ],
            'modelo' => [
                'base_uri' => 'https://doc.staging.modelo.fr',
            ],
        ]);

        $container
            ->register('autowired', SimpleWiredStub::class)
            ->setPublic(true)
            ->setAutowired(true);

        $container->compile();

        $service = $container->get('autowired');

        $this->assertInstanceOf(ModeloClientInterface::class, $service->getModeloClient());

        $this->expectException(AuthKeyMissingException::class);

        $service->getModeloClient()->getAuthKey();
    }

    private function createContainer(array $configs = []): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => __DIR__,
            'kernel.root_dir' => __DIR__,
            'kernel.project_dir' => __DIR__,
            'kernel.build_dir' => __DIR__,
            'kernel.runtime_environment' => 'test',
            'kernel.charset' => 'UTF-8',
            'kernel.environment' => 'test',
            'kernel.debug' => false,
            'kernel.bundles_metadata' => [],
            'kernel.container_class' => 'AutowiringTestContainer',
            'debug.file_link_format' => null,
            'kernel.bundles' => [
                'FrameworkBundle' => FrameworkBundle::class,
                'ModeloBundle' => ModeloBundle::class,
            ],
            'env(base64:default::SYMFONY_DECRYPTION_SECRET)' => 'dummy',
        ]));

        $container->set('kernel', function()
        {
            return new Kernel('test', false);
        });

        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new ModeloExtension());

        foreach ($configs as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }

        return $container;
    }
}

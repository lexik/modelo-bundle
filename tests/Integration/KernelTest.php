<?php

namespace Choosit\ModeloBundle\Tests\Integration;

use Choosit\ModeloBundle\Exception\AuthKeyMissingException;
use Choosit\ModeloBundle\Service\ModeloClientInterface;
use Choosit\ModeloBundle\Tests\Integration\Stubs\Kernel as KernelStub;
use Choosit\ModeloBundle\Tests\Integration\Stubs\SimpleWiredStub;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/ModeloBundle/');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::ensureKernelShutdown();
    }

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new KernelStub('test', true, $options['config'] ?? 'base');
    }

    public function testLoadedBaseConfig(): void
    {
        self::bootKernel(['config' => 'base']);

        $config = self::container()->getParameter('modelo_config');
        $service = self::container()->get(ModeloClientInterface::class);
        $simplewired = self::container()->get(SimpleWiredStub::class);

        $this->assertArrayHasKey('base_uri', $config);
        $this->assertArrayHasKey('agency_code', $config['auth']);
        $this->assertArrayHasKey('private_key', $config['auth']);
        $this->assertNotNull($simplewired->getModeloClient()->getAuthKey());
        $this->assertSame($service, $simplewired->getModeloClient());
    }

    public function testLoadedEmptyConfig(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('base_uri parameter must be defined.');

        self::bootKernel(['config' => 'empty']);
    }

    public function testLoadedMinimalConfig(): void
    {
        self::bootKernel(['config' => 'minimal']);

        $config = self::container()->getParameter('modelo_config');
        $service = self::container()->get(ModeloClientInterface::class);
        $simplewired = self::container()->get(SimpleWiredStub::class);

        $this->assertSame($service, $simplewired->getModeloClient());

        $this->expectException(AuthKeyMissingException::class);
        $this->expectExceptionMessage('authKey couldn\'t generated, you may have forgot to fill in the configuration file or setAuthKey with agencyCode and/or privateKey in your config file.');
        $this->assertNull($simplewired->getModeloClient()->getAuthKey());
    }

    private static function container(): ContainerInterface
    {
        /* @phpstan-ignore-next-line */
        if (KernelStub::IS_LEGACY) {
            return self::$container;
        }

        return self::getContainer();
    }
}

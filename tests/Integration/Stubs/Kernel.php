<?php

namespace Choosit\ModeloBundle\Tests\Integration\Stubs;

use Choosit\ModeloBundle\ModeloBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /* @phpstan-ignore-next-line */
    public const IS_LEGACY = 5 > BaseKernel::MAJOR_VERSION;

    /**
     * @var string
     */
    private $config;

    public function __construct(string $environment, bool $debug, string $config = 'base')
    {
        parent::__construct($environment, $debug);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new ModeloBundle(),
            new Bundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/ModeloBundle/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/ModeloBundle/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(sprintf(__DIR__.'/../config/%s_config.yaml', $this->config));
    }
}

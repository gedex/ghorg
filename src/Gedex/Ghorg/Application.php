<?php

namespace Gedex\Ghorg;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends BaseApplication {
    /**
     * Config instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance of GithubClient.
     *
     * @var \Github\Client
     */
    protected $githubClient;

    /**
     * ContainerBuilder
     *
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param string         $name    Application's name
     * @param string         $version Application's version
     * @param Config         $config  Instance of Config
     * @param \Github\Client $config  Instance of Config
     */
    public function __construct($name, $version, Config $config, \Github\Client $githubClient)
    {
        parent::__construct($name, $version);

        $this->config = $config;
        $this->githubClient = $githubClient;
    }

    /**
     * Get container.
     *
     * @return ContainerBuilder Container
     */
    public function getContainer()
    {
        if (null === $this->container) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    /**
     * Create and returns a new container.
     *
     * @return ContainerBuilder Container
     */
    protected function createContainer()
    {
        $container = new ContainerBuilder();

        $container->set('config', $this->config);
        $container->set('githubClient', $this->githubClient);

        return $container;
    }
}

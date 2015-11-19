<?php

namespace Gedex\Ghorg\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command {
    /**
     * Get service from application's container.
     *
     * @param  string $serviceName Service's name
     * @return mixed               Service
     */
    protected function get($serviceName)
    {
        return $this->getApplication()->getContainer()->get($serviceName);
    }
}

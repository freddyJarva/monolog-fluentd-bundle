<?php

namespace HiQ\MonologFluentdBundle;

use HiQ\MonologFluentdBundle\DependencyInjection\HiQMonologFluentdExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HiQMonologFluentdBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new HiQMonologFluentdExtension();
        }
        return $this->extension;
    }
}
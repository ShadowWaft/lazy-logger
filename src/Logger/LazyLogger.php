<?php

namespace Logger;

namespace ShadowWaft\LazyLoggerBundle\Logger;

use Psr\Log\LoggerInterface;

abstract class LazyLogger
{
    protected LoggerInterface $logger;

    final public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}

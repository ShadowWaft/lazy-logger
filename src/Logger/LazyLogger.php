<?php

namespace Logger;

namespace ShadowWaft\LazyLogger\Logger;

use Psr\Log\LoggerInterface;

abstract class LazyLogger
{
    protected LoggerInterface $logger;

    final public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}

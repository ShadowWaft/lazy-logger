<?php

namespace ShadowWaft\LazyLogger\Logger;

use Psr\Log\LoggerInterface;

trait LazyLoggerTrait
{
    private ?LoggerInterface $logger = null;

    public function setLazyLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
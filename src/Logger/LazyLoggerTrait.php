<?php

namespace ShadowWaft\LazyLogger\Logger;

use Psr\Log\LoggerInterface;

trait LazyLoggerTrait
{
    protected LoggerInterface $logger;

    public function setLazyLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
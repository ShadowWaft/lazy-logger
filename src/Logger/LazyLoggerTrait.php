<?php

namespace ShadowWaft\LazyLogger\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LazyLoggerTrait
{
    protected ?LoggerInterface $logger = null;

    public function setLazyLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function __get($name)
    {
        if ($name === 'logger') {
            return $this->logger ?? new NullLogger();
        }

        return null;
    }
}
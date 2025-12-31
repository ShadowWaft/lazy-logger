<?php

namespace ShadowWaft\LazyLoggerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class LazyLog
{
    public function __construct(
        public string $channel,
        public string $level = 'debug'
    ) {}
}

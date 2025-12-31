<?php

namespace ShadowWaft\LazyLogger\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class LazyLog
{
    # TODO consider env based debug level config (dev = debug, prod = error) - Consider configuration by config.yml
    /**
     * @param string $channel Name / Domain of the files / log file where logs are written into for example: 'user' for UserController, UserRepository, UserService ...
     * @param string $level Logging level (debug, info, warning, error ...)
     */
    public function __construct(
        public string $channel,
        public string $level = 'debug'
    ) {}
}

<?php

namespace ShadowWaft\LazyLogger\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ShadowWaft\LazyLogger\Attribute\LazyLog;
use ReflectionClass;

class LazyLoggerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(LazyLog::class);
            if (empty($attributes)) {
                continue;
            }

            $config = $attributes[0]->newInstance();
            $channel = $config->channel;

            $loggerId = "lazy_logger.$channel";

            if (!$container->hasDefinition($loggerId)) {
                $loggerDef = $container->register($loggerId, Logger::class);
                $loggerDef->addArgument($channel);

                $handlerDef = $container->register(
                    "lazy_logger.handler.$channel",
                    StreamHandler::class
                );
                $handlerDef->addArgument("%kernel.logs_dir%/$channel.log");
                $handlerDef->addArgument(Logger::toMonologLevel($config->level));

                $loggerDef->addMethodCall(
                    'pushHandler',
                    [new Reference("lazy_logger.handler.$channel")]
                );
            }

            $definition->addMethodCall(
                'setLazyLogger',
                [new Reference($loggerId)]
            );
        }
    }
}

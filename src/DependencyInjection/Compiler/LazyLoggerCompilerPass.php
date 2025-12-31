<?php

namespace ShadowWaft\LazyLoggerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ShadowWaft\LazyLoggerBundle\Logger\LazyLogger;
use ShadowWaft\LazyLoggerBundle\Attribute\LazyLog;
use ReflectionClass;

class LazyLoggerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            if (!$class || !is_subclass_of($class, LazyLogger::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(LazyLog::class);

            if (empty($attributes)) {
                continue; // explizit nur per Attribute
            }

            /** @var LazyLog $config */
            $config = $attributes[0]->newInstance();
            $channel = $config->channel;

            $loggerId = "lazy_logger.$channel";

            if (!$container->hasDefinition($loggerId)) {
                // Logger
                $loggerDef = $container->register($loggerId, Logger::class);
                $loggerDef->addArgument($channel);

                // Handler
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
                'setLogger',
                [new Reference($loggerId)]
            );
        }
    }
}

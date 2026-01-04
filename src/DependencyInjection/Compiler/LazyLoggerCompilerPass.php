<?php

namespace ShadowWaft\LazyLogger\DependencyInjection\Compiler;

use Monolog\Level;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ShadowWaft\LazyLogger\Attribute\LazyLog;
use ShadowWaft\LazyLogger\Logger\LazyLoggerTrait;
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

            // Skip abstract definitions - they are used for inheritance only
            if ($definition->isAbstract()) {
                continue;
            }

            if (!$this->classUsesTraitRecursive($class, LazyLoggerTrait::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            $attributes = $reflection->getAttributes(LazyLog::class);
            $channel = 'default';
            $level = Level::Debug;

            if (!empty($attributes)) {
                $config = $attributes[0]->newInstance();
                $channel = $config->channel;
                $level = Logger::toMonologLevel($config->level);
            }

            $loggerId = "lazy_logger.$channel";

            if (!$container->hasDefinition($loggerId)) {
                $loggerDef = $container->register($loggerId, Logger::class);
                $loggerDef->addArgument($channel);

                $handlerDef = $container->register(
                    "lazy_logger.handler.$channel",
                    StreamHandler::class
                );
                $handlerDef->addArgument("%kernel.logs_dir%/$channel.log");
                $handlerDef->addArgument($level);

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

    /**
     * Recursively checks if a class uses a specific trait in its hierarchy.
     *
     * @param string $class The class name to check
     * @param string $trait The trait to look for
     * @return bool True if the trait is used anywhere in the class hierarchy
     */
    private function classUsesTraitRecursive(string $class, string $trait): bool
    {
        $usedTraits = [];

        // Traverse the entire class hierarchy
        do {
            $traits = class_uses($class);
            if ($traits === false) {
                break;
            }
            $usedTraits = array_merge($usedTraits, $traits);
        } while ($class = get_parent_class($class));

        return in_array($trait, $usedTraits);
    }
}

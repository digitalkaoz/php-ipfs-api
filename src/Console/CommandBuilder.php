<?php

declare(strict_types=1);

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IPFS\Console;

use ArgumentsResolver\NamedArgumentsResolver;
use IPFS\Api;
use IPFS\Client;
use IPFS\Driver\Driver;
use IPFS\Utils\AnnotationReader;
use IPFS\Utils\CaseFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandBuilder
{
    /**
     * @var array|Api\Api[]
     */
    private $apis;
    /**
     * @var AnnotationReader
     */
    private $reader;
    /**
     * @var array|Driver[]
     */
    private $drivers = [];

    public function __construct(array $apis, AnnotationReader $reader)
    {
        $this->apis = $apis;
        $this->reader = $reader;
    }

    /**
     * @return array|ApiCommand[]
     */
    public function generateCommands(): array
    {
        $commands = [];

        foreach ($this->apis as $class) {
            $api = new \ReflectionClass($class);

            foreach ($api->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($this->reader->isApi($method)) {
                    $command = $this->generateCommand($class, $api->getShortName(), $method);
                    $commands[$command->getName()] = $command;
                }
            }
        }

        return $commands;
    }

    private function generateCommand(Api\Api $api, string $name, \ReflectionMethod $method): Command
    {
        $command = new ApiCommand(strtolower($this->reader->getName($method)));

        return $command
            ->setDefinition($this->buildDefinition($method))
            ->setDescription($this->reader->getDescription($method))
            ->setCode($this->createCode($api, $name, $method))
        ;
    }

    private function buildDefinition(\ReflectionMethod $method): InputDefinition
    {
        $definition = new InputDefinition();

        $parameters = $this->reader->getParameters($method);

        foreach ($parameters as $name => $param) {
            $name = CaseFormatter::camelToDash($name);

            if ($param->hasDefault()) {
                $mode = InputOption::VALUE_NONE;
                $default = $param->getDefault();

                if (true === $default || is_string($default)) {
                    $mode = InputOption::VALUE_OPTIONAL;
                } elseif (is_bool($default)) {
                    $default = null;
                } elseif (!is_string($default)) {
                    $mode = InputOption::VALUE_REQUIRED;
                }

                $definition->addOption(new InputOption($name, null, $mode, $param->getDescription(), $default));

                continue;
            }

            $definition->addArgument(new InputArgument($name, InputArgument::REQUIRED, $param->getDescription(), null));
        }

        return $definition;
    }

    private function createCode(Api\Api $api, $name, \ReflectionMethod $method): \Closure
    {
        return function (InputInterface $input, OutputInterface $output) use ($name, $method, $api) {
            $fn = $method->getClosure($api);

            $options = CaseFormatter::dashToCamelArray($input->getOptions());
            $arguments = CaseFormatter::dashToCamelArray($input->getArguments());

            $args = (new NamedArgumentsResolver($method))->resolve(array_merge($options, $arguments));
            $args = $this->sanitizeArguments($args);

            $client = new Client($this->chooseClient($input->getOption('driver')));
            $response = $client->execute($fn(...$args));

            $output->writeln($response);
        };
    }

    public function addDriver(Driver $driver): CommandBuilder
    {
        $this->drivers[get_class($driver)] = $driver;

        return $this;
    }

    private function chooseClient(string $class): Driver
    {
        if (!isset($this->drivers[$class])) {
            throw new \InvalidArgumentException(sprintf('"%s" is an unknown Driver, please add it with "addDriver"', $class));
        }

        return $this->drivers[$class];
    }

    private function sanitizeArguments(array $args): array
    {
        foreach ($args as $index => $value) {
            $args[$index] = CaseFormatter::stringToBool($value);
        }

        return $args;
    }
}

<?php

declare(strict_types=1);

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/digitalkaoz/php-ipfs>
 */

namespace IPFS\Console;

use ArgumentsResolver\NamedArgumentsResolver;
use IPFS\Api;
use IPFS\Client;
use IPFS\Utils\AnnotationReader;
use IPFS\Utils\CaseFormatter;
use Pimple\Container;
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
     * @var Container
     */
    private $container;

    public function __construct(array $apis, Container $container)
    {
        $this->apis = $apis;
        $this->container = $container;

        $this->reader = $this->container[AnnotationReader::class];
    }

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

            $client = new Client($this->container[$input->getOption('driver')]);
            $response = $client->execute($fn(...$args));

            $output->writeln($response);
            //dump($response, json_decode($response, true));
            //$output->writeln(print_r(json_decode($response, true), true));
        };
    }

    public function sanitizeArguments(array $args): array
    {
        foreach ($args as $index => $value) {
            $args[$index] = CaseFormatter::stringToBool($value);
        }

        return $args;
    }
}

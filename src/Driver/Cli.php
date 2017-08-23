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

namespace IPFS\Driver;

use IPFS\Command\Command;
use IPFS\Utils\AnnotationReader;
use IPFS\Utils\CaseFormatter;
use Symfony\Component\Process\Process;

class Cli implements Driver
{
    /**
     * @var Process
     */
    private $builder;
    /**
     * @var string
     */
    private $binary;
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct(Process $builder, AnnotationReader $reader, $binary = 'ipfs')
    {
        $this->builder = $builder;
        $this->binary = $binary;
        $this->reader = $reader;
    }

    public function execute(Command $command)
    {
        //SYMFONY 3.2 compat
        $script = implode(' ', array_map(['\\Symfony\\Component\\Process\\ProcessUtils', 'escapeArgument'], $this->buildCommand($command)));

        $process = $this->builder
            ->setCommandLine($script)
            ->enableOutput()
            ->inheritEnvironmentVariables()
            ->setWorkingDirectory(getenv('CWD'))
        ;

        $process->start();
        $process->wait();

        return $process->getOutput() ?: $process->getErrorOutput();
    }

    private function buildCommand(Command $command): array
    {
        return array_merge(
            [$this->binary],
            explode(':', str_replace('basics:', '', $command->getAction())),
            $this->parseParameters($command)
        );
    }

    private function parseParameters(Command $command): array
    {
        $parameters = $this->reader->getParameters($command->getMethod());

        $parsedParameters = [];

        foreach ($command->getArguments() as $name => $value) {
            if ($parameters[$name]->hasDefault() && $parameters[$name]->getDefault() !== $value) {
                $parsedParameters[] = sprintf('--%s=%s', CaseFormatter::camelToDash($name), var_export($value, true));
                continue;
            }

            if (!$parameters[$name]->hasDefault()) {
                $parsedParameters[] = $value;
                continue;
            }
        }

        return $parsedParameters;
    }
}

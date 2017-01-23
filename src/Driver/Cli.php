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
use Symfony\Component\Process\ProcessBuilder;

class Cli implements Driver
{
    /**
     * @var ProcessBuilder
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

    public function __construct(ProcessBuilder $builder, AnnotationReader $reader, $binary = 'ipfs')
    {
        $this->builder = $builder;
        $this->binary = $binary;
        $this->reader = $reader;
    }

    public function execute(Command $command)
    {
        $process = $this->builder
            ->setArguments($this->buildCommand($command))
            ->enableOutput()
            ->inheritEnvironmentVariables()
            ->setWorkingDirectory(getenv('CWD'))
            ->getProcess()
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

            throw new \LogicException(sprintf('"%s" is neither an option nor an argument', $name));
        }

        return $parsedParameters;
    }
}

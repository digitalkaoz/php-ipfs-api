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
        $process = ($this->builder)::create($this->buildCommand($command))
            ->enableOutput()
            ->inheritEnvironmentVariables()
            ->setWorkingDirectory(getenv('CWD'))
            ->getProcess();

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

        foreach ($command->getArgs() as $name => $value) {
            if ($parameters[$name]->hasDefault() && $parameters[$name]->getDefault() !== $value) {
                $parsedParameters[] = sprintf('--%s=%s', CaseFormatter::camelToDash($name), var_export($value, true));
                continue;
            }

            if (!$parameters[$name]->hasDefault()) {
                $parsedParameters[] = $value;
                continue;
            }

            //TODO whoopsi?
        }

        return $parsedParameters;
    }
}

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

namespace IPFS\Utils;

use Doctrine\Common\Annotations\AnnotationReader as BaseReader;
use IPFS\Annotation\Api;
use IPFS\Annotation\Param;
use phpDocumentor\Reflection\DocBlockFactory;

class AnnotationReader
{
    /**
     * @var BaseReader
     */
    private $reader;
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    public function __construct(BaseReader $reader, DocBlockFactory $docBlockFactory)
    {
        $this->reader = $reader;
        $this->docBlockFactory = $docBlockFactory;
    }

    public function isPrimary($method): bool
    {
        return $this->getApi($method)->primary;
    }

    public function isApi($method): bool
    {
        return (bool) $this->getApi($method);
    }

    public function getDescription($method): string
    {
        return $this->docBlockFactory->create($this->getMethod($method))->getSummary();
    }

    public function getName($method): string
    {
        $reflection = $this->getMethod($method);

        $prefix = null;

        if (!$this->isPrimary($method) && !$this->getApi($method)->name) {
            $prefix = $reflection->getDeclaringClass()->getShortName() . ':';
        }

        return $prefix . ($this->getApi($method)->name ?: CaseFormatter::camelToColon($reflection->name));
    }

    /**
     * @return array|Param[]
     */
    public function getParameters($method): array
    {
        $reflection = $this->getMethod($method);
        $parameters = [];
        $docBlock = $this->docBlockFactory->create($reflection->getDocComment());
        $params = $docBlock->getTagsByName('param');

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[$parameter->name] = new Param(
                $parameter->name,
                $this->parseParameterDocblock($params, $parameter),
                $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : Param::class
            );
        }

        return $parameters;
    }

    public function getApi($method): Api
    {
        return $this->reader->getMethodAnnotation(
            $this->getMethod($method),
            Api::class
        );
    }

    public function getMethod($method): \ReflectionMethod
    {
        if ($method instanceof \ReflectionMethod) {
            return $method;
        }

        list($api, $method) = explode('::', $method);

        return new \ReflectionMethod($api, $method);
    }

    private function parseParameterDocblock(array $params, \ReflectionParameter $parameter): string
    {
        foreach ($params as $param) {
            if ($parameter->name === $param->getVariableName()) {
                return $param->getDescription()->render();
            }
        }

        return '';
    }
}

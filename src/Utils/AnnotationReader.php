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
        return (string) $this->getApi($method)->name;
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

    /**
     * @return Api|null
     */
    private function getApi($method)
    {
        return $this->reader->getMethodAnnotation(
            $this->getMethod($method),
            Api::class
        );
    }

    private function getMethod($method): \ReflectionMethod
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

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

namespace IPFS\Command;

use IPFS\Utils\CaseFormatter;

class Command
{
    /**
     * @var array
     */
    private $args = [];
    /**
     * @var string
     */
    private $method;

    public function __construct(string $method, array $args = [])
    {
        $this->args = $args;
        $this->method = $method;
    }

    public function getAction(): string
    {
        $parts = explode('\\', $this->method);
        $shortName = array_pop($parts);

        return CaseFormatter::camelToColon(str_replace('::', ':', $shortName));
    }

    public function getArguments(): array
    {
        return $this->args;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}

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

namespace IPFS\Annotation;

class Param
{
    private $name;

    private $description;

    private $default;

    public function __construct(string $name, string $description, $default = __CLASS__)
    {
        $this->name = $name;
        $this->description = $description;
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default === __CLASS__ ? null : $this->default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasDefault(): bool
    {
        return $this->default !== __CLASS__;
    }
}

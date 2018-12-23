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

namespace IPFS\Api;

class ApiBuilder
{
    /**
     * @var ApiParser
     */
    private $parser;
    /**
     * @var ApiGenerator
     */
    private $generator;

    public function __construct(ApiParser $parser, ApiGenerator $generator)
    {
        $this->parser = $parser;
        $this->generator = $generator;
    }

    public function build($url = 'https://docs.ipfs.io/reference/api/http/', $prefix = '#api-v0')
    {
        //parse
        $config = $this->parser->build($url, $prefix);

        //dump
        $this->generator->build($config);
    }
}

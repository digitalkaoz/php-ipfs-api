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

use Camel\Format\FormatInterface;

class ConfigurableFormatter implements FormatInterface
{
    /**
     * @var string
     */
    private $delimiter;

    public function __construct(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function split($word)
    {
        return explode($this->delimiter, $word);
    }

    /**
     * {@inheritdoc}
     */
    public function join(array $words)
    {
        // Ensure words are lowercase
        $words = array_map('strtolower', $words);

        return implode($this->delimiter, $words);
    }
}

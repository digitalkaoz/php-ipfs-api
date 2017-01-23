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

namespace spec\IPFS;

use IPFS\Annotation\Api as Endpoint;
use IPFS\Api\Api;
use IPFS\Command\Command;

class TestApi implements Api
{
    /**
     * this is a description.
     *
     * @Endpoint(name="test:foo")
     *
     * @param string $bar  bar
     * @param bool   $bazz bazz
     * @param int    $lol  lol
     */
    public function foo(string $bar, bool $bazz = false, int $lol = 1)
    {
        return new Command(__METHOD__, get_defined_vars());
    }
}

<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS;

use IPFS\Client;
use IPFS\Command\Command;
use IPFS\Driver\Driver;
use PhpSpec\ObjectBehavior;

class ClientSpec extends ObjectBehavior
{
    public function let(Driver $driver)
    {
        $this->beConstructedWith($driver);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
        $this->shouldImplement(Driver::class);
    }

    public function it_passes_the_command_to_the_underlying_driver(Driver $driver, Command $command)
    {
        $driver->execute($command)->shouldBeCalled();

        $this->execute($command);
    }
}

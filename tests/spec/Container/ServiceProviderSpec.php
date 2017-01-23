<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Container;

use IPFS\Container\ServiceProvider;
use PhpSpec\ObjectBehavior;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Prophecy\Argument;

class ServiceProviderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ServiceProvider::class);
        $this->shouldImplement(ServiceProviderInterface::class);
    }

    public function it_registers_services(Container $container)
    {
        $container->offsetSet(Argument::type('string'), Argument::type('callable'))->shouldBeCalled();

        $this->register($container);
    }
}

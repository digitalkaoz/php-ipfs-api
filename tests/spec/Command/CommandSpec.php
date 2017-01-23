<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Command;

use IPFS\Command\Command;
use PhpSpec\ObjectBehavior;

class CommandSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(__CLASS__ . '::fooBarBazz', ['foo', 'bar']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Command::class);
    }

    public function it_has_accessors_for_method_and_arguments()
    {
        $this->getMethod()->shouldBe(__CLASS__ . '::fooBarBazz');
        $this->getArguments()->shouldBe(['foo', 'bar']);
    }

    public function it_can_convert_the_method_to_an_action()
    {
        $this->getAction()->shouldBe('command:spec:foo:bar:bazz');
    }
}

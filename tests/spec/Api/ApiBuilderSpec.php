<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Api;

use IPFS\Api\ApiBuilder;
use IPFS\Api\ApiGenerator;
use IPFS\Api\ApiParser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiBuilderSpec extends ObjectBehavior
{
    public function let(ApiParser $parser, ApiGenerator $builder)
    {
        $this->beConstructedWith($parser, $builder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApiBuilder::class);
    }

    public function it_invokes_the_parser_and_generator(ApiParser $parser, ApiGenerator $builder)
    {
        $parser->build(Argument::type('string'), Argument::type('string'))->shouldBeCalled()->willReturn([]);
        $builder->build(Argument::type('array'))->shouldBeCalled();

        $this->build();
    }
}

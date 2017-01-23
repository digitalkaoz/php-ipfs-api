<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Annotation;

use IPFS\Annotation\Param;
use PhpSpec\ObjectBehavior;

class ParamSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo', 'bar', 'lol');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Param::class);
    }

    public function it_has_simple_getters()
    {
        $this->getName()->shouldBe('foo');
        $this->getDescription()->shouldBe('bar');
        $this->getDefault()->shouldBe('lol');
    }

    public function it_can_check_if_there_is_a_default_value()
    {
        $this->beConstructedWith('foo', 'bar');
        $this->hasDefault()->shouldBe(false);
        $this->getDefault()->shouldBe(null);
    }

    public function it_can_check_if_there_is_no_default_value()
    {
        $this->beConstructedWith('foo', 'bar', 'lol');
        $this->hasDefault()->shouldBe(true);
        $this->getDefault()->shouldBe('lol');
    }
}

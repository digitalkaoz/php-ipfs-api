<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Utils;

use IPFS\Utils\CaseFormatter;
use PhpSpec\ObjectBehavior;

class CaseFormatterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(CaseFormatter::class);
    }

    public function it_can_convert_camelCase_to_colonCase_and_vice_versa()
    {
        $this->camelToColon('fooBar')->shouldBe('foo:bar');
        $this->colonToCamel('foo:bar')->shouldBe('fooBar');
    }

    public function it_can_convert_camelCase_to_dashCase_and_vice_versa()
    {
        $this->camelToDash('fooBar')->shouldBe('foo-bar');
        $this->dashToCamel('foo-bar')->shouldBe('fooBar');
    }

    public function it_can_convert_string_representations_of_booleans_to_booleans()
    {
        $this->stringToBool('True')->shouldBe(true);
        $this->stringToBool('true')->shouldBe(true);
        $this->stringToBool('false')->shouldBe(false);
        $this->stringToBool('FALSE')->shouldBe(false);

        $this->stringToBool('foo')->shouldBe('foo');
    }

    public function it_can_convert_all_values_from_an_array_from_dash_to_camel()
    {
        $this->dashToCamelArray(['foo-bar' => 'foo', 'bar-bazz-lol' => 'bar'])->shouldBe(['fooBar' => 'foo', 'barBazzLol' => 'bar']);
    }
}

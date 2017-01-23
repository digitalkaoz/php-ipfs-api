<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/digitalkaoz/php-ipfs>
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

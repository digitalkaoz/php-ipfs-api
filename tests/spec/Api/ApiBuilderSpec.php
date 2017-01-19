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

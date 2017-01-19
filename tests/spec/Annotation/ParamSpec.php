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

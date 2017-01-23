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

use IPFS\Annotation\Param;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpSpec\ObjectBehavior;

class AnnotationReaderSpec extends ObjectBehavior
{
    const METHOD = 'spec\IPFS\TestApi::foo';

    public function let()
    {
        $this->beConstructedWith(new \Doctrine\Common\Annotations\AnnotationReader(), DocBlockFactory::createInstance());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AnnotationReader::class);
    }

    public function it_can_handle__METHOD___constant_as_input()
    {
        $this->isApi(self::METHOD)->shouldBe(true);
    }

    public function it_can_handle_reflections_as_input()
    {
        $reflection = new \ReflectionMethod(self::METHOD);
        $this->isApi($reflection)->shouldBe(true);
    }

    public function it_can_parse_the_description_from_the_docblock()
    {
        $this->getDescription(self::METHOD)->shouldBe('this is a description.');
    }

    public function it_can_parse_the_name_from_the_annotation()
    {
        $this->getName(self::METHOD)->shouldBe('test:foo');
    }

    public function it_can_the_method_parameters()
    {
        $parameters = $this->getParameters(self::METHOD);

        $parameters->shouldHaveCount(3);

        $parameters['bar']->shouldHaveType(Param::class);
        $parameters['bar']->hasDefault()->shouldBe(false);
        $parameters['bar']->getName()->shouldBe('bar');
        $parameters['bar']->getDescription()->shouldBe('bar');

        $parameters['bazz']->shouldHaveType(Param::class);
        $parameters['bazz']->hasDefault()->shouldBe(true);
        $parameters['bazz']->getDefault()->shouldBe(false);
        $parameters['bazz']->getName()->shouldBe('bazz');
        $parameters['bazz']->getDescription()->shouldBe('bazz');

        $parameters['lol']->shouldHaveType(Param::class);
        $parameters['lol']->hasDefault()->shouldBe(true);
        $parameters['lol']->getDefault()->shouldBe(1);
        $parameters['lol']->getName()->shouldBe('lol');
        $parameters['lol']->getDescription()->shouldBe('lol');
    }
}

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

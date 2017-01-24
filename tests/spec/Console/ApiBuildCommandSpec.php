<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Console;

use IPFS\Api\ApiBuilder;
use IPFS\Console\ApiBuildCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiBuildCommandSpec extends ObjectBehavior
{
    public function let(ApiBuilder $builder)
    {
        $this->beConstructedWith(function () use ($builder) {
            return $builder->getWrappedObject();
        });
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApiBuildCommand::class);
        $this->shouldHaveType(Command::class);
    }

    public function it_has_a_descriptive_name()
    {
        $this->getName()->shouldBe('rebuild');
    }

    public function it_calls_the_builder(ApiBuilder $builder, InputInterface $input, OutputInterface $output)
    {
        $this->run($input, $output);

        $builder->build()->shouldHaveBeenCalled();
    }
}

<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Driver;

use IPFS\Command\Command;
use IPFS\Driver\Cli;
use IPFS\Driver\Driver;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

class CliSpec extends ObjectBehavior
{
    const METHOD = 'spec\IPFS\TestApi::foo';

    public function let()
    {
        $this->beConstructedWith(
            new Process([]),
            new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader(), DocBlockFactory::createInstance()),
            'echo'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cli::class);
        $this->shouldImplement(Driver::class);
    }

    public function it_creates_a_cli_command_and_passes_it_to_the_binary()
    {
        $this->execute(new Command(self::METHOD, ['bar' => 'bar', 'bazz' => true, 'lol' => 10]))->shouldBe("test api foo bar --bazz=true --lol=10\n");
    }
}

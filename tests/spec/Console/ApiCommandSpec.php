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

use IPFS\Console\ApiCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ApiCommandSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo');

        $this->setApplication(new Application());

        $definition = new InputDefinition([
            new InputOption('--quiet', null, InputOption::VALUE_NONE, 'my own quiet'),
        ]);

        $this->setDefinition($definition);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApiCommand::class);
        $this->shouldHaveType(Command::class);
    }

    public function it_only_appends_the_default_options_if_they_are_not_already_exists()
    {
        $this->mergeApplicationDefinition(true);
    }
}

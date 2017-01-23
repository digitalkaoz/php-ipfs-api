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
use IPFS\Console\CommandBuilder;
use IPFS\Container\ServiceProvider;
use IPFS\Driver\Driver;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpSpec\ObjectBehavior;
use Pimple\Container;
use Prophecy\Argument;
use spec\IPFS\TestApi;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandBuilderSpec extends ObjectBehavior
{
    public function let()
    {
        $container = new Container();
        $provider = new ServiceProvider();
        $provider->register($container);

        $this->beConstructedWith(
            [new TestApi()],
            new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader(), DocBlockFactory::createInstance())
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommandBuilder::class);
    }

    public function it_builds_symfony_commands_from_the_apis()
    {
        $commands = $this->generateCommands();

        $commands->shouldHaveKey('test:foo');
        $command = $commands['test:foo'];
        /* @var ApiCommand $command */

        $command->shouldHaveType(ApiCommand::class);
        $command->getName()->shouldBe('test:foo');
        $command->getDescription()->shouldBe('this is a description.');

        $command->getDefinition()->hasOption('bazz');
        $command->getDefinition()->getOption('bazz')->getDefault()->shouldBe(false);
        $command->getDefinition()->getOption('bazz')->getDescription()->shouldBe('bazz');

        $command->getDefinition()->hasOption('lol');
        $command->getDefinition()->getOption('lol')->getDefault()->shouldBe(1);
        $command->getDefinition()->getOption('lol')->getDescription()->shouldBe('lol');

        $command->getDefinition()->hasArgument('bar');
        $command->getDefinition()->getArgument('bar')->isRequired()->shouldBe(true);
        $command->getDefinition()->getArgument('bar')->getDescription()->shouldBe('bar');
    }

    public function it_invokes_the_driver_on_run(Driver $driver, OutputInterface $output)
    {
        $this->getWrappedObject()->addDriver($driver->getWrappedObject());

        $command = $this->generateCommands()['test:foo'];
        $command->setApplication(new Application());

        $command->run(new ArrayInput(['bar' => 'real value', '--driver' => get_class($driver->getWrappedObject())]), $output);

        $output->writeln(Argument::any())->shouldBeCalled();
    }
}

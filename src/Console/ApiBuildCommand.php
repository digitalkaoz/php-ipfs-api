<?php

declare(strict_types=1);

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IPFS\Console;

use IPFS\Api\ApiBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiBuildCommand extends Command
{
    /**
     * @var ApiBuilder
     */
    private $builder;

    public function __construct(ApiBuilder $builder)
    {
        parent::__construct(null);
        $this->builder = $builder;
    }

    protected function configure()
    {
        $this
            ->setName('rebuild')
            ->setDescription('rebuild api classes by parsing the official api doc')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->builder->build();

        $output->writeln('updated Api Classes in <info>src/Api</info>');
    }
}

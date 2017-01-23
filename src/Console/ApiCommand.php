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

use IPFS\Driver\Http;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputOption;

class ApiCommand extends Command
{
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        $originalOptions = $this->getApplication()->getDefinition()->getOptions();
        $this->getApplication()->getDefinition()->setOptions([]);

        parent::mergeApplicationDefinition($mergeArgs);

        foreach ($originalOptions as $option) {
            try {
                $this->getDefinition()->addOption($option);
            } catch (LogicException $e) {
                //option already defined by command itself
            }
        }

        $this->getDefinition()->addOption(new InputOption('--driver', '', InputOption::VALUE_REQUIRED, 'which driver to use', Http::class));

        $this->getApplication()->getDefinition()->setOptions($originalOptions);
    }
}

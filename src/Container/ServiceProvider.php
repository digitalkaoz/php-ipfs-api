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

namespace IPFS\Container;

use Http\Client\Common\PluginClient;
use Http\Client\HttpAsyncClient;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use IPFS\Api;
use IPFS\Console\ApiBuildCommand;
use IPFS\Console\CommandBuilder;
use IPFS\Driver\Cli;
use IPFS\Driver\Http;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\BuilderFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $this->registerDriver($pimple);
        $this->registerConsole($pimple);

        $pimple[AnnotationReader::class] = function () {
            return new AnnotationReader(
                new \Doctrine\Common\Annotations\AnnotationReader(),
                DocBlockFactory::createInstance()
            );
        };

        $pimple[Api\ApiBuilder::class] = function () use ($pimple) {
            $this->registerHttp($pimple);

            $parser = new Api\ApiParser($pimple[HttpAsyncClient::class], $pimple[MessageFactory::class], new Crawler());
            $generator = new Api\ApiGenerator(new BuilderFactory());

            return new Api\ApiBuilder($parser, $generator);
        };
    }

    public function registerHttp(Container $pimple)
    {
        $pimple[HttpAsyncClient::class] = function () {
            return new PluginClient(
                HttpAsyncClientDiscovery::find(),
                []
            );
        };

        $pimple[MessageFactory::class] = function () {
            return MessageFactoryDiscovery::find();
        };

        $pimple[UriFactory::class] = function () {
            return UriFactoryDiscovery::find();
        };
    }

    private function registerDriver(Container $pimple)
    {
        $pimple[Http::class] = function () use ($pimple) {
            $this->registerHttp($pimple);

            return new Http(
                $pimple[HttpAsyncClient::class],
                $pimple[MessageFactory::class],
                $pimple[UriFactory::class],
                $pimple[AnnotationReader::class],
                getenv('IPFS_API') ?: 'http://localhost:5001/api/v0'
            );
        };

        $pimple[Cli::class] = function () use ($pimple) {
            return new Cli(new Process([]), $pimple[AnnotationReader::class], getenv('IPFS_BINARY') ?: 'ipfs');
        };
    }

    private function registerConsole(Container $pimple)
    {
        $pimple[CommandBuilder::class] = function (Container $pimple) {
            $builder = new CommandBuilder([
                new Api\Basics(),
                new Api\Bitswap(),
                new Api\Block(),
                new Api\Bootstrap(),
                new Api\Config(),
                new Api\Dag(),
                new Api\Dht(),
                new Api\Diag(),
                new Api\File(),
                new Api\Files(),
                new Api\Filestore(),
                new Api\Key(),
                new Api\Log(),
                new Api\Name(),
                new Api\CObject(),
                new Api\P2p(),
                new Api\Pin(),
                new Api\Pubsub(),
                new Api\Refs(),
                new Api\Repo(),
                new Api\Stats(),
                new Api\Swarm(),
                new Api\Tar(),
            ], $pimple[AnnotationReader::class]);

            $builder->addDriver(Cli::class, $pimple->raw(Cli::class));
            $builder->addDriver(Http::class, $pimple->raw(Http::class));

            return $builder;
        };

        $pimple[Application::class] = function (Container $pimple) {
            $app = new Application('ipfs', '@git-version@');
            $app->addCommands($pimple[CommandBuilder::class]->generateCommands());
            $app->add(new ApiBuildCommand($pimple->raw(Api\ApiBuilder::class)));

            return $app;
        };
    }
}

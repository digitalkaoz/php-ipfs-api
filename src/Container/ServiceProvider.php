<?php

declare(strict_types=1);

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
use Symfony\Component\Process\ProcessBuilder;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $this->registerDriver($pimple);
        $this->registerHttp($pimple);
        $this->registerConsole($pimple);

        $pimple[AnnotationReader::class] = function () {
            return new AnnotationReader(
                new \Doctrine\Common\Annotations\AnnotationReader(),
                DocBlockFactory::createInstance()
            );
        };

        $pimple[Api\ApiBuilder::class] = function (Container $pimple) {
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
        $pimple[Http::class] = function (Container $pimple) {
            return new Http(
                $pimple[HttpAsyncClient::class],
                $pimple[MessageFactory::class],
                $pimple[UriFactory::class],
                $pimple[AnnotationReader::class],
                getenv('IPFS_API') ?: 'http://localhost:5001/api/v0'
            );
        };

        $pimple[Cli::class] = function (Container $pimple) {
            return new Cli(new ProcessBuilder(), $pimple[AnnotationReader::class], getenv('IPFS_BINARY') ?: 'ipfs');
        };
    }

    private function registerConsole(Container $pimple)
    {
        $pimple[CommandBuilder::class] = function (Container $pimple) {
            return new CommandBuilder([
                new Api\Basics(),
                new Api\Bitswap(),
                new Api\Block(),
                new Api\Bootstrap(),
                new Api\Config(),
                new Api\Dht(),
                new Api\Diag(),
                new Api\File(),
                new Api\Files(),
                new Api\Log(),
                new Api\Name(),
                new Api\Object(),
                new Api\Pin(),
                new Api\Refs(),
                new Api\Repo(),
                new Api\Stats(),
                new Api\Swarm(),
                new Api\Tar(),
                new Api\Tour(),
            ], $pimple);
        };

        $pimple[Application::class] = function (Container $pimple) {
            $app = new Application('ipfs', '@git-version@');
            $app->addCommands($pimple[CommandBuilder::class]->generateCommands());
            $app->add(new ApiBuildCommand($pimple));

            return $app;
        };
    }
}

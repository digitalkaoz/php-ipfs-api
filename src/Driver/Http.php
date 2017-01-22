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

namespace IPFS\Driver;

use Http\Client\HttpAsyncClient;
use Http\Message\MessageFactory;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Http\Message\UriFactory;
use IPFS\Command\Command;
use IPFS\Utils\AnnotationReader;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Http implements Driver
{
    /**
     * @var HttpAsyncClient
     */
    private $client;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var UriFactory
     */
    private $uriFactory;
    /**
     * @var string
     */
    private $baseUri;
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct(HttpAsyncClient $client, MessageFactory $messageFactory, UriFactory $uriFactory, AnnotationReader $reader, $baseUri = 'http://localhost:5001/api/v0')
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->uriFactory = $uriFactory;
        $this->baseUri = $baseUri;
        $this->reader = $reader;
    }

    public function execute(Command $command)
    {
        $request = $this->buildRequest($command, $this->getConfig($command));

        return $this->client->sendAsyncRequest($request)->then(function (ResponseInterface $response) {
            return $response->getBody()->getContents();
        })->wait();
    }

    private function buildRequest(Command $command, $config): RequestInterface
    {
        $config['method'] = isset($config['method']) ? $config['method'] : 'GET';
        $body = 'POST' === $config['method'] ? $this->buildBody($command) : null;

        return $this->messageFactory->createRequest(
            $config['method'],
            $this->buildUri($command, $config),
            $this->buildHeaders($body),
            $body ? $body->build() : null
        );
    }

    private function buildUri(Command $command, array $config): UriInterface
    {
        $uri = $this->uriFactory->createUri($this->baseUri . $config['path']);
        $vars = [];

        foreach ($command->getArgs() as $name => $value) {
            if (is_string($value) && is_readable($value)) {
                continue;
            }

            $vars[$name] = $value;
        }

        //fix arg1= arg2= to arg= weird but seems to be correct (doubled query names)
        $query = preg_replace('/(\d+)=/', '=', http_build_query($vars));

        return $uri->withQuery($query);
    }

    private function buildBody(Command $command): MultipartStreamBuilder
    {
        $builder = new MultipartStreamBuilder();

        foreach ($command->getArgs() as $name => $value) {
            if (is_string($value) && is_readable($value)) {
                $builder->addResource($name, fopen($command->getArgs()[$name], 'rb'), ['filename' => $value, 'headers' => ['Content-Type' => 'application/octet-stream']]);
            }
        }

        return $builder;
    }

    private function buildHeaders(MultipartStreamBuilder $body = null): array
    {
        return [
            'User-Agent'   => 'php-ipfs',
            'Content-Type' => $body ? 'multipart/form-data; boundary=' . $body->getBoundary() : null,
        ];
    }

    private function getConfig(Command $command): array
    {
        $config = [
            'path'   => '/' . str_replace(':', '/', $this->reader->getName($command->getMethod())),
            'method' => 'GET',
        ];

        foreach ($command->getArgs() as $arg) {
            if (is_string($arg) && is_readable($arg)) {
                $config['method'] = 'POST';
                break;
            }
        }

        return $config;
    }
}

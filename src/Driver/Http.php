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
            return (string) $response->getBody()->getContents();
        })->wait();
    }

    private function buildRequest(Command $command, $config): RequestInterface
    {
        $body = $this->buildBody($command);

        return $this->messageFactory->createRequest(
            $config['method'],
            $this->buildUri($command, $config),
            $this->buildHeaders($body),
            $body->build()
        );
    }

    private function buildUri(Command $command, array $config): UriInterface
    {
        $uri = $this->uriFactory->createUri($this->baseUri . $config['path']);
        $vars = [];

        foreach ($command->getArguments() as $name => $value) {
            if (is_string($value) && is_readable($value)) {
                continue;
            }

            $vars[$name] = $value;
        }

        //fix arg1= arg2= to arg= weird but seems to be correct (doubled query variable names)
        $query = preg_replace('/(\d+)=/', '=', http_build_query($vars));

        return $uri->withQuery($query);
    }

    private function buildBody(Command $command): MultipartStreamBuilder
    {
        $builder = new MultipartStreamBuilder();

        foreach ($command->getArguments() as $name => $value) {
            if (is_string($value) && is_readable($value)) {
                $builder->addResource($name, fopen($command->getArguments()[$name], 'rb'), [
                    'filename' => $value,
                    'headers'  => ['Content-Type' => 'application/octet-stream'],
                ]);
            }
        }

        return $builder;
    }

    private function buildHeaders(MultipartStreamBuilder $body): array
    {
        $emptyBody = '--' . $body->getBoundary() . "--\r\n";

        return [
            'User-Agent'   => 'php-ipfs',
            'Content-Type' => $body->build()->getContents() !== $emptyBody ? 'multipart/form-data; boundary=' . $body->getBoundary() : null,
        ];
    }

    private function getConfig(Command $command): array
    {
        $config = [
            'path'   => '/' . str_replace(':', '/', $this->reader->getName($command->getMethod())),
            'method' => 'GET',
        ];

        foreach ($command->getArguments() as $arg) {
            if (is_string($arg) && is_readable($arg)) {
                $config['method'] = 'POST';
                break;
            }
        }

        return $config;
    }
}

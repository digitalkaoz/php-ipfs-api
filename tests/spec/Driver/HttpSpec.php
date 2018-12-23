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

use Http\Client\HttpAsyncClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Promise\Promise;
use IPFS\Command\Command;
use IPFS\Driver\Driver;
use IPFS\Driver\Http;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;

class HttpSpec extends ObjectBehavior
{
    const METHOD = 'spec\IPFS\TestApi::foo';

    public function let(HttpAsyncClient $client)
    {
        $this->beConstructedWith(
            $client,
            MessageFactoryDiscovery::find(),
            UriFactoryDiscovery::find(),
            new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader(), DocBlockFactory::createInstance())
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Http::class);
        $this->shouldImplement(Driver::class);
    }

    public function it_creates_a_http_uri_from_the_command_and_calls_the_rest_api(HttpAsyncClient $client, Promise $promise)
    {
        $client->sendAsyncRequest(Argument::that(function (RequestInterface $request) {
            return 'bar=bar&bazz=1&lol=10' === $request->getUri()->getQuery() &&
                '/api/v0/test/foo' === $request->getUri()->getPath()
            ;
        }))->willReturn($promise);

        $promise->then(Argument::type('callable'))->willReturn($promise);
        $promise->wait()->shouldBeCalled();

        $this->execute(new Command(self::METHOD, ['bar' => 'bar', 'bazz' => true, 'lol' => 10]))->shouldBe(null);
    }

    public function it_can_attach_variables_that_are_file_locations_as_multipart_form_data_to_the_request(HttpAsyncClient $client, Promise $promise)
    {
        $client->sendAsyncRequest(Argument::that(function (RequestInterface $request) {
            $body = $request->getBody()->getContents();

            return 'bazz=1&lol=10' === $request->getUri()->getQuery() &&
                '/api/v0/test/foo' === $request->getUri()->getPath() &&
                false !== strpos($request->getHeaders()['Content-Type'][0], 'multipart/form-data; boundary=') &&
                false !== strpos($body, 'Content-Type: application/octet-stream') &&
                false !== strpos($body, 'Content-Disposition: form-data; name="bar"; filename="HttpSpec.php"')
            ;
        }))->willReturn($promise);

        $promise->then(Argument::type('callable'))->willReturn($promise);
        $promise->wait()->shouldBeCalled();

        $this->execute(new Command(self::METHOD, ['bar' => __FILE__, 'bazz' => true, 'lol' => 10]))->shouldBe(null);
    }
}

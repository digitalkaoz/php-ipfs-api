<?php

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
            return $request->getUri()->getQuery() === 'bar=bar&bazz=1&lol=10' &&
                $request->getUri()->getPath() === '/api/v0/test/foo'
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

            return $request->getUri()->getQuery() === 'bazz=1&lol=10' &&
                $request->getUri()->getPath() === '/api/v0/test/foo' &&
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

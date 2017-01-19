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

namespace IPFS\Api;

use Http\Client\HttpAsyncClient;
use Http\Message\MessageFactory;
use IPFS\Utils\CaseFormatter;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class ApiParser
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
     * @var Crawler
     */
    private $crawler;

    public function __construct(HttpAsyncClient $client, MessageFactory $messageFactory, Crawler $crawler)
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->crawler = $crawler;
    }

    public function build(string $url = 'https://ipfs.io/docs/api/', string $prefix = '#apiv0'): array
    {
        return $this->client
            ->sendAsyncRequest($this->messageFactory->createRequest('GET', $url))
            ->then(function (ResponseInterface $response) use ($prefix) {
                return $this->buildMethods($response, $prefix);
            })
            ->wait()
        ;
    }

    private function buildMethods(ResponseInterface $response, $prefix): array
    {
        $this->crawler->addHtmlContent($response->getBody()->getContents());
        $links = $this->crawler->filter('li a[href^="' . $prefix . '"]')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        $config = [];
        foreach ($links as $link) {
            $anchor = $this->crawler->filter($link)->first();
            $description = $anchor->nextAll()->first()->getNode(0)->nodeName === 'p' ? $anchor->nextAll()->first()->text() : null;

            $methodConfig = [
                'parts'       => str_replace('/', ':', str_replace('/api/v0/', '', $anchor->text())),
                'description' => $description,
                'arguments'   => $this->parseArguments($anchor, $description ? 2 : 1),
            ];

            $nameParts = explode(':', $methodConfig['parts']);
            if (count($nameParts) > 1) {
                $class = array_shift($nameParts);
                $methodConfig['class'] = ucfirst(CaseFormatter::dashToCamel($class));
                $methodConfig['method'] = CaseFormatter::dashToCamel(implode('-', $nameParts));
            } else {
                $methodConfig['class'] = 'Basics';
                $methodConfig['method'] = CaseFormatter::dashToCamel($methodConfig['parts']);
            }

            $config[$methodConfig['class']][] = $methodConfig;
        }

        return $config;
    }

    private function parseArguments(Crawler $anchor, int $index): array
    {
        $argumentsRootNode = $anchor->nextAll()->eq($index)->first();

        if ($argumentsRootNode->getNode(0)->nodeName === 'ul') {
            $names = [];

            return $argumentsRootNode->filter('li')->each(function (Crawler $argument) use (&$names) {
                $description = $argument->filter('code')->first()->getNode(0)->nextSibling->textContent;
                $name = CaseFormatter::dashToCamel($argument->filter('code')->first()->text());
                if (!isset($names[$name])) {
                    $names[$name] = 0;
                } else {
                    ++$names[$name];
                }

                $config = [
                    'name'        => $names[$name] === 0 ? $name : $name . $names[$name],
                    'required'    => $argument->filter('strong')->count() > 0,
                    'description' => $this->parseDescription($description),
                    'default'     => $this->parseDefaultValue($description),
                    'type'        => $this->parseTypeHint($description),
                ];

                //fixup files
                if ('file' === $config['type']) {
                    $config['type'] = 'string';
                    $config['name'] = 'file';
                }

                return $config;
            });
        }

        return [];
    }

    private function parseDefaultValue(string $description)
    {
        preg_match('/Default\: \"(.+)\"/', $description, $default);

        if (count($default) === 2) {
            $default = $default[1];
            if (in_array($default, ['true', 'false', 'TRUE', 'FALSE'], true)) {
                $default = filter_var($default, FILTER_VALIDATE_BOOLEAN);
            }

            return $default;
        }
    }

    private function parseDescription($description): string
    {
        preg_match('/\]\: (.+)/', $description, $default);

        if (count($default) > 1) {
            return substr($default[1], 0, strpos($default[1], '.') ?: strlen($default[1])) . '.';
        }
    }

    private function parseTypeHint(string $description): string
    {
        preg_match('/\[(.+)\]/', $description, $default);

        if (count($default) > 1) {
            return $default[1];
        }
    }
}

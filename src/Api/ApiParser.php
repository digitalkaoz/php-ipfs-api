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

    public function build(string $url = 'https://docs.ipfs.io/reference/api/http/', string $prefix = '#api-v0'): array
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
        $links = $this->crawler->filter('main li a[href^="' . $prefix . '"]')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        $config = [];
        foreach ($links as $link) {
            $link = $this->fixDocumentationLinks($link);

            $anchor = $this->crawler->filter($link)->first();
            $description = 'p' === $anchor->nextAll()->first()->getNode(0)->nodeName ? $anchor->nextAll()->first()->text() : null;

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

        if ('ul' === $argumentsRootNode->getNode(0)->nodeName) {
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
                    'name'        => 0 === $names[$name] ? $name : $name . $names[$name],
                    'required'    => $argument->filter('strong')->count() > 0,
                    'description' => $this->parseDescription($description),
                    'default'     => $this->parseDefaultValue($description),
                    'type'        => $this->parseTypeHint($description),
                ];

                $config['default'] = 'bool' === $config['type'] && null === $config['default'] ? false : $config['default'];

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
        preg_match('/Default.* [‘|"|“]([^\.]+)[’|"|”]\./', $description, $default);

        if (2 === count($default)) {
            $default = preg_replace('/[^\x00-\x7f]/', '', $default[1]);

            return CaseFormatter::stringToBool($default);
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
        preg_match('/\[([^\]]+)\]/', $description, $default);

        if (count($default) > 1) {
            return $default[1];
        }
    }

    private function fixDocumentationLinks(string $link): string
    {
        if ('#api-v0-statsrepo' === $link) {
            return '#api-v0-stats-repo';
        }

        return $link;
    }
}

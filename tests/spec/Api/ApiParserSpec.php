<?php

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\IPFS\Api;

use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use IPFS\Api\ApiParser;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DomCrawler\Crawler;

class ApiParserSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(HttpAsyncClientDiscovery::find(), MessageFactoryDiscovery::find(), new Crawler());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApiParser::class);
    }

    public function it_can_parse_the_api_docs_correctly()
    {
        ob_start();
        @passthru('curl -I https://ipfs.io/docs/api/', $response);
        ob_end_clean();

        if (0 !== $response) {
            throw new SkippingException('no internet connection available, so skip test');
        }

        $config = $this->build();

        $config->shouldBeArray();
        $config->shouldHaveKey('Basics');
        $addApi = $config['Basics'][0];

        $addApi->shouldBeLike([
            'parts'       => 'add',
            'description' => 'Add a file or directory to ipfs.',
            'arguments'   => [
                [
                    'name'        => 'file',
                    'required'    => true,
                    'description' => 'The path to a file to be added to ipfs.',
                    'default'     => null,
                    'type'        => 'string',
                ], [
                    'name'        => 'recursive',
                    'required'    => false,
                    'description' => 'Add directory paths recursively.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'quiet',
                    'required'    => false,
                    'description' => 'Write minimal output.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'quieter',
                    'required'    => false,
                    'description' => 'Write only final hash.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'silent',
                    'required'    => false,
                    'description' => 'Write no output.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'progress',
                    'required'    => false,
                    'description' => 'Stream progress data.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'trickle',
                    'required'    => false,
                    'description' => 'Use trickle-dag format for dag generation.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'onlyHash',
                    'required'    => false,
                    'description' => 'Only chunk and hash - do not write to disk.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'wrapWithDirectory',
                    'required'    => false,
                    'description' => 'Wrap files with a directory object.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'hidden',
                    'required'    => false,
                    'description' => 'Include files that are hidden.',
                    'default'     => false,
                    'type'        => 'bool',
                ], [
                    'name'        => 'chunker',
                    'required'    => false,
                    'description' => 'Chunking algorithm to use.',
                    'default'     => null,
                    'type'        => 'string',
                ], [
                    'name'        => 'pin',
                    'required'    => false,
                    'description' => 'Pin this object when adding.',
                    'default'     => true,
                    'type'        => 'bool',
                ], [
                    'name'        => 'rawLeaves',
                    'required'    => false,
                    'description' => 'Use raw blocks for leaf nodes.',
                    'default'     => false,
                    'type'        => 'bool',
                ],
                [
                    'name'        => 'nocopy',
                    'required'    => false,
                    'description' => 'Add the file using filestore.',
                    'default'     => false,
                    'type'        => 'bool',
                ],
                [
                    'name'        => 'fscache',
                    'required'    => false,
                    'description' => 'Check the filestore for pre-existing blocks.',
                    'default'     => false,
                    'type'        => 'bool',
                ], ],
            'class'       => 'Basics',
            'method'      => 'add',
        ]);
    }
}

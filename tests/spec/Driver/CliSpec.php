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

use IPFS\Command\Command;
use IPFS\Driver\Cli;
use IPFS\Driver\Driver;
use IPFS\Utils\AnnotationReader;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\ProcessBuilder;

class CliSpec extends ObjectBehavior
{
    const METHOD = 'spec\IPFS\TestApi::foo';

    public function let()
    {
        $this->beConstructedWith(
            new ProcessBuilder(),
            new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader(), DocBlockFactory::createInstance()),
            'echo'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Cli::class);
        $this->shouldImplement(Driver::class);
    }

    public function it_creates_a_cli_command_and_passes_it_to_the_binary()
    {
        $this->execute(new Command(self::METHOD, ['bar' => 'bar', 'bazz' => true, 'lol' => 10]))->shouldBe("test api foo bar --bazz=true --lol=10\n");
    }
}

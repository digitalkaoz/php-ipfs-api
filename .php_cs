<?php


$header = <<<EOF
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

This software consists of voluntary contributions made by many individuals
and is licensed under the MIT license. For more information, see
<https://github.com/digitalkaoz/php-ipfs>
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('bin')
    ->in(getenv('PWD')); // this may not be correct in a non-docker environment

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'               => true,
        'header_comment'         => ['header' => $header],
        '@PSR2'                  => true,
        '@PSR1'                  => true,
        'array_syntax'           => ['syntax' => 'short'],
        'ordered_imports'        => true,
        'strict_comparison'      => true,
        'strict_param'          => true,
        'phpdoc_order'           => true,
        'no_useless_return'      => true,
        'ereg_to_preg'           => true,
        'concat_space'           => ['spacing' => 'one'],
        'binary_operator_spaces' => ['align_equals' => false, 'align_double_arrow' => true],
    ])
//->setUsingLinter(false)
    ->setFinder($finder)
    ->setUsingCache(false);

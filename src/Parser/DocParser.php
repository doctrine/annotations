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
 * <http://www.doctrine-project.org>.
 */

declare(strict_types=1);

namespace Doctrine\Annotations\Parser;

use Doctrine\Annotations\Context;
use Doctrine\Annotations\Builder;
use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Exception\ParserException;

use Hoa\Compiler\Exception as HoaException;

/**
 * A parser for docblock annotations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class DocParser
{
    /**
     * @var \Doctrine\Annotations\Resolver
     */
    protected $resolver;

    /**
     * @var \Doctrine\Annotations\Builder
     */
    private $builder;

    /**
     * @var \Doctrine\Annotations\Parser\HoaParser
     */
    private $parser;

    /**
     * Constructor
     *
     * @param \Doctrine\Annotations\Parser\HoaParser $parser
     * @param \Doctrine\Annotations\Builder          $builder
     * @param \Doctrine\Annotations\Resolver         $resolver
     */
    public function __construct(HoaParser $parser, Builder $builder, Resolver $resolver)
    {
        $this->parser   = $parser;
        $this->builder  = $builder;
        $this->resolver = $resolver;
    }

    /**
     * @param string                        $docblock
     * @param \Doctrine\Annotations\Context $context
     *
     * @return array
     */
    public function parse(string $docblock, Context $context)
    {
        try {
            $ignoreNotImported = $context->getIgnoreNotImported();
            $visitor           = new DocVisitor($context, $this->builder, $this->resolver, $ignoreNotImported);
            $result            = $this->parser->parseDockblock($docblock, $visitor);

            return $result;
        } catch (HoaException $e) {
            throw ParserException::hoaException($e, $context->getDescription());
        }
    }
}

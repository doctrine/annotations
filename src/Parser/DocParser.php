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
use Doctrine\Annotations\Exception\ParserException;

use Hoa\Compiler\Llk\Llk;
use Hoa\File\Read;

/**
 * A parser for docblock annotations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class DocParser
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var HoaParser
     */
    private $parser;

    /**
     * Whether annotations that have not been imported should be ignored.
     *
     * @var bool
     */
    private $ignoreNotImported;

    /**
     * Constructor
     *
     * @param Builder $builder
     * @param bool    $ignoreNotImported
     */
    public function __construct(HoaParser $parser, Builder $builder, bool $ignoreNotImported = false)
    {
        $this->parser            = $parser;
        $this->builder           = $builder;
        $this->ignoreNotImported = $ignoreNotImported;
    }

    /**
     * @param string  $docblock
     * @param Context $context
     */
    public function parse(string $docblock, Context $context)
    {
        try {
            $visitor = new DocVisitor($context, $this->builder, $this->ignoreNotImported);
            $result  = $this->parser->parseDockblock($docblock, $visitor);

            return $result;
        } catch (\Hoa\Compiler\Exception $e) {
            throw ParserException::hoaException($e, $context->getDescription());
        }
    }
}

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

namespace Doctrine\Common\Annotations;

use Doctrine\Common\Lexer\AbstractLexer;

use function ctype_alpha;
use function is_numeric;
use function str_replace;
use function stripos;
use function strlen;
use function strpos;
use function strtolower;
use function substr;

/**
 * Simple lexer for docblock annotations.
 */
final class DocLexer extends AbstractLexer
{
    public const T_NONE    = 1;
    public const T_INTEGER = 2;
    public const T_STRING  = 3;
    public const T_FLOAT   = 4;

    // All tokens that are also identifiers should be >= 100
    public const T_IDENTIFIER          = 100;
    public const T_AT                  = 101;
    public const T_CLOSE_CURLY_BRACES  = 102;
    public const T_CLOSE_PARENTHESIS   = 103;
    public const T_COMMA               = 104;
    public const T_EQUALS              = 105;
    public const T_FALSE               = 106;
    public const T_NAMESPACE_SEPARATOR = 107;
    public const T_OPEN_CURLY_BRACES   = 108;
    public const T_OPEN_PARENTHESIS    = 109;
    public const T_TRUE                = 110;
    public const T_NULL                = 111;
    public const T_COLON               = 112;
    public const T_MINUS               = 113;

    /** @var array */
    protected $noCase = [
        '@'  => self::T_AT,
        ','  => self::T_COMMA,
        '('  => self::T_OPEN_PARENTHESIS,
        ')'  => self::T_CLOSE_PARENTHESIS,
        '{'  => self::T_OPEN_CURLY_BRACES,
        '}'  => self::T_CLOSE_CURLY_BRACES,
        '='  => self::T_EQUALS,
        ':'  => self::T_COLON,
        '-'  => self::T_MINUS,
        '\\' => self::T_NAMESPACE_SEPARATOR,
    ];

    /** @var array */
    protected $withCase = [
        'true'  => self::T_TRUE,
        'false' => self::T_FALSE,
        'null'  => self::T_NULL,
    ];

    /**
     * Whether the next token starts immediately, or if there were
     * non-captured symbols before that
     */
    public function nextTokenIsAdjacent(): bool
    {
        return $this->token === null
            || ($this->lookahead !== null
                && ($this->lookahead['position'] - $this->token['position']) === strlen($this->token['value']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns()
    {
        return [
            '[a-z_\\\][a-z0-9_\:\\\]*[a-z_][a-z0-9_]*',
            '(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?',
            '"(?:""|[^"])*+"',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getNonCatchablePatterns()
    {
        return ['\s+', '\*+', '(.)'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        if ($value[0] === '"') {
            $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

            return self::T_STRING;
        }

        if (isset($this->noCase[$value])) {
            return $this->noCase[$value];
        }

        if ($value[0] === '_' || $value[0] === '\\' || ctype_alpha($value[0])) {
            return self::T_IDENTIFIER;
        }

        $lowerValue = strtolower($value);

        if (isset($this->withCase[$lowerValue])) {
            return $this->withCase[$lowerValue];
        }

        // Checking numeric value
        if (is_numeric($value)) {
            return strpos($value, '.') !== false || stripos($value, 'e') !== false
                ? self::T_FLOAT : self::T_INTEGER;
        }

        return $type;
    }
}

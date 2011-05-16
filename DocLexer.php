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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

/**
 * Simple lexer for docblock annotations.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DocLexer
{
    const T_NONE                = 1;
    const T_IDENTIFIER          = 2;
    const T_INTEGER             = 3;
    const T_STRING              = 4;
    const T_FLOAT               = 5;

    const T_AT                  = 101;
    const T_CLOSE_CURLY_BRACES  = 102;
    const T_CLOSE_PARENTHESIS   = 103;
    const T_COMMA               = 104;
    const T_EQUALS              = 105;
    const T_FALSE               = 106;
    const T_NAMESPACE_SEPARATOR = 107;
    const T_OPEN_CURLY_BRACES   = 108;
    const T_OPEN_PARENTHESIS    = 109;
    const T_TRUE                = 110;
    const T_NULL                = 111;

    /**
     * @var array Array of scanned tokens
     */
    private $tokens = array();

    /**
     * @var integer Current lexer position in input string
     */
    private $position = 0;

    /**
     * @var integer Current peek of current lexer position
     */
    private $peek = 0;

    /**
     * @var array The next token in the input.
     */
    public $lookahead;

    /**
     * @var array The last matched/seen token.
     */
    public $token;

    /**
     * Sets the input data to be tokenized.
     *
     * The Lexer is immediately reset and the new input tokenized.
     * Any unprocessed tokens from any previous input are lost.
     *
     * @param string $input The input to be tokenized.
     */
    public function setInput($input)
    {
        $this->tokens = array();
        $this->reset();
        $this->scan($input);
    }

    /**
     * Resets the lexer.
     */
    public function reset()
    {
        $this->lookahead = null;
        $this->lookbehind = null;
        $this->token = null;
        $this->peek = 0;
        $this->position = 0;
    }

    /**
     * Resets the peek pointer to 0.
     */
    public function resetPeek()
    {
        $this->peek = 0;
    }

    /**
     * Resets the lexer position on the input to the given position.
     *
     * @param integer $position Position to place the lexical scanner
     */
    public function resetPosition($position = 0)
    {
        $this->position = $position;
    }

    /**
     * Checks whether a given token matches the current lookahead.
     *
     * @param integer|string $token
     * @return boolean
     */
    public function isNextToken($token)
    {
        return $this->lookahead['type'] === $token;
    }

    /**
     * Moves to the next token in the input string.
     *
     * A token is an associative array containing three items:
     *  - 'value'    : the string value of the token in the input string
     *  - 'type'     : the type of the token (identifier, numeric, string, input
     *                 parameter, none)
     *  - 'position' : the position of the token in the input string
     *
     * @return array|null the next token; null if there is no more tokens left
     */
    public function moveNext()
    {
        $this->peek = 0;
        $this->lookbehind = $this->token;
        $this->token = $this->lookahead;
        $this->lookahead = (isset($this->tokens[$this->position]))
            ? $this->tokens[$this->position++] : null;

        return $this->lookahead !== null;
    }

    /**
     * Tells the lexer to skip input tokens until it sees a token with the given value.
     *
     * @param $type The token type to skip until.
     */
    public function skipUntil($type)
    {
        while ($this->lookahead !== null && $this->lookahead['type'] !== $type) {
            $this->moveNext();
        }
    }

    /**
     * Checks if given value is identical to the given token
     *
     * @param mixed $value
     * @param integer $token
     * @return boolean
     */
    public function isA($value, $token)
    {
        return $this->getType($value) === $token;
    }

    /**
     * Moves the lookahead token forward.
     *
     * @return array | null The next token or NULL if there are no more tokens ahead.
     */
    public function peek()
    {
        if (isset($this->tokens[$this->position + $this->peek])) {
            return $this->tokens[$this->position + $this->peek++];
        } else {
            return null;
        }
    }

    /**
     * Peeks at the next token, returns it and immediately resets the peek.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function glimpse()
    {
        $peek = $this->peek();
        $this->peek = 0;
        return $peek;
    }

    /**
     * Gets the literal for a given token.
     *
     * @param integer $token
     * @return string
     */
    public function getLiteral($token)
    {
        $className = get_class($this);
        $reflClass = new \ReflectionClass($className);
        $constants = $reflClass->getConstants();

        foreach ($constants as $name => $value) {
            if ($value === $token) {
                return $className . '::' . $name;
            }
        }

        return $token;
    }

    /**
     * Scans the input string for tokens.
     *
     * @param string $input a query string
     */
    protected function scan($input)
    {
        static $regex;

        if ( ! isset($regex)) {
            $regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')|'
                   . implode('|', $this->getNonCatchablePatterns()) . '/i';
        }

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);

        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $type = $this->getType($match[0]);

            $this->tokens[] = array(
                'value' => $match[0],
                'type'  => $type,
                'position' => $match[1],
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function getCatchablePatterns()
    {
        return array(
            '[a-z_][a-z0-9_:]*',
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
            '"(?:[^"]|"")*"',
        );
    }

    /**
     * @inheritdoc
     */
    protected function getNonCatchablePatterns()
    {
        return array('\s+', '\*+', '(.)');
    }

    /**
     * @inheritdoc
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        // Checking numeric value
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false || stripos($value, 'e') !== false)
                ? self::T_FLOAT : self::T_INTEGER;
        }

        if ($value[0] === '"') {
            $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));

            return self::T_STRING;
        } else {
            switch (strtolower($value)) {
                case '@':
                    return self::T_AT;

                case ',':
                    return self::T_COMMA;

                case '(':
                    return self::T_OPEN_PARENTHESIS;

                case ')':
                    return self::T_CLOSE_PARENTHESIS;

                case '{':
                    return self::T_OPEN_CURLY_BRACES;

                case '}':
                    return self::T_CLOSE_CURLY_BRACES;

                case '=':
                    return self::T_EQUALS;

                case '\\':
                    return self::T_NAMESPACE_SEPARATOR;

                case 'true':
                    return self::T_TRUE;

                case 'false':
                    return self::T_FALSE;

                case 'null':
                    return self::T_NULL;

                default:
                    if (ctype_alpha($value[0]) || $value[0] === '_') {
                        return self::T_IDENTIFIER;
                    }

                    break;
            }
        }

        return $type;
    }
}
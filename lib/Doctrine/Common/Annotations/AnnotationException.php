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

/**
 * Description of AnnotationException
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class AnnotationException extends \Exception
{
    function __construct($message, $code = 0, \Exception $previous = null, AbstractLexer $lexer = null)
    {
        if ($lexer) {
            $message .= "Annotation read so far\n" . $lexer->getOriginalUntilNow() . "\n";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Creates a new AnnotationException describing a Syntax error.
     *
     * @param string $message Exception message
     * @param AbstractLexer $lexer The lexer being used.
     * @return AnnotationException
     */
    public static function syntaxError($message, AbstractLexer $lexer = null)
    {
        return new self('[Syntax Error] ' . $message, 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing a Semantical error.
     *
     * @param string $message Exception message
     * @param AbstractLexer $lexer The lexer being used.
     * @return AnnotationException
     */
    public static function semanticalError($message, AbstractLexer $lexer = null)
    {
        return new self('[Semantical Error] ' . $message, 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing a constant semantical error.
     *
     * @since 2.3
     * @param string $identifier
     * @param string $context
     * @return AnnotationException
     */
    public static function semanticalErrorConstants($identifier, $context = null, AbstractLexer $lexer = null)
    {
        return self::semanticalError(sprintf(
            "Couldn't find constant %s%s", $identifier,
            $context ? ", $context." : "."
        ), 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing an error which occurred during
     * the creation of the annotation.
     *
     * @since 2.2
     * @param string $message
     * @return AnnotationException
     */
    public static function creationError($message, AbstractLexer $lexer = null)
    {
        return new self('[Creation Error] ' . $message, 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing an type error of an attribute.
     *
     * @since 2.2
     * @param string $attributeName
     * @param string $annotationName
     * @param string $context
     * @param string $expected
     * @param mixed $actual
     * @param AbstractLexer $lexer The lexer being used.
     * @return AnnotationException
     */
    public static function typeError($attributeName, $annotationName, $context, $expected, $actual, AbstractLexer $lexer = null)
    {
        return new self(sprintf(
            '[Type Error] Attribute "%s" of @%s declared on %s expects %s, but got %s.',
            $attributeName,
            $annotationName,
            $context,
            $expected,
            is_object($actual) ? 'an instance of '.get_class($actual) : gettype($actual)
        ), 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing an required error of an attribute.
     *
     * @since 2.2
     * @param string $attributeName
     * @param string $annotationName
     * @param string $context
     * @param string $expected
     * @param AbstractLexer $lexer The lexer being used.
     * @return AnnotationException
     */
    public static function requiredError($attributeName, $annotationName, $context, $expected, AbstractLexer $lexer = null)
    {
        return new self(sprintf(
            '[Type Error] Attribute "%s" of @%s declared on %s expects %s. This value should not be null.',
            $attributeName,
            $annotationName,
            $context,
            $expected
        ), 0, null, $lexer);
    }

    /**
     * Creates a new AnnotationException describing a invalid enummerator.
     *
     * @since 2.4
     * @param string $attributeName
     * @param string $annotationName
     * @param string $context
     * @param array  $available
     * @param mixed  $given
     * @param AbstractLexer $lexer The lexer being used.
     * @return AnnotationException
     */
    public static function enumeratorError($attributeName, $annotationName, $context, $available, $given, AbstractLexer $lexer = null)
    {
        throw new self(sprintf(
            '[Enum Error] Attribute "%s" of @%s declared on %s accept only [%s], but got %s.',
            $attributeName,
            $annotationName,
            $context,
            implode(', ', $available),
            is_object($given) ? get_class($given) : $given
        ), 0, null, $lexer);
    }

    /**
     * @return AnnotationException
     */
    public static function optimizerPlusSaveComments()
    {
        throw new self("You have to enable opcache.save_comments=1 or zend_optimizerplus.save_comments=1.");
    }
}

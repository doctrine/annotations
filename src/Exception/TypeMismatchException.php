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

namespace Doctrine\Annotations\Exception;

/**
 * Thrown to indicate that provided atrribute of an annotation is of wrong type.
 *
 * @author Fabio B. Silva <fabio.bat.silva@hotmail.com>
 */
class TypeMismatchException extends AnnotationException
{
    /**
     * Creates a new AnnotationException describing a invalid enummerator.
     *
     * @param string $attributeName
     * @param string $annotationName
     * @param string $context
     * @param array  $available
     * @param mixed  $given
     *
     * @return AnnotationException
     */
    public static function enumeratorError($attributeName, $annotationName, $context, $available, $given) : self
    {
        $format  = 'Attribute "%s" of @%s declared on %s accept only [%s], but got %s.';
        $options = implode(', ', $available);
        $label   = is_object($given)
            ? get_class($given)
            : $given;

        return new self(sprintf(
            $format,
            $attributeName,
            $annotationName,
            $context,
            $options,
            $label
        ));
    }

    /**
     * Creates a new AnnotationException describing an type error of an attribute.
     *
     * @param string $attributeName
     * @param string $annotationName
     * @param string $context
     * @param string $expected
     * @param mixed  $actual
     *
     * @return AnnotationException
     */
    public static function attributeTypeError(string $attributeName, string $annotationName, string $context, string $expected, $actual) : self
    {
        $format = 'Attribute "%s" of @%s declared on %s expects %s, but got %s.';
        $label  = is_object($actual)
            ? 'an instance of ' . get_class($actual)
            : gettype($actual);

        return new self(sprintf(
            $format,
            $attributeName,
            $annotationName,
            $context,
            $expected,
            $label
        ));
    }
}

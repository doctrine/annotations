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

namespace Doctrine\Annotations\Annotation;

/**
 * Annotation that can be used to signal to the parser
 * to check the annotation target during the parsing process.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 *
 * @Annotation
 */
final class Target
{
    const TARGET_CLASS              = 1;
    const TARGET_METHOD             = 2;
    const TARGET_PROPERTY           = 4;
    const TARGET_ANNOTATION         = 8;
    const TARGET_ALL                = 15;

    /**
     * @var array
     */
    private static $map = array(
        'ALL'        => self::TARGET_ALL,
        'CLASS'      => self::TARGET_CLASS,
        'METHOD'     => self::TARGET_METHOD,
        'PROPERTY'   => self::TARGET_PROPERTY,
        'ANNOTATION' => self::TARGET_ANNOTATION,
    );

    /**
     * @var array
     */
    public $value;

    /**
     * Target as bitmask.
     *
     * @var integer
     */
    public $target;

    /**
     * Annotation constructor.
     *
     * @param array $values
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        if ( ! isset($values['value'])){
            $values['value'] = null;
        }

        if (is_string($values['value'])){
            $values['value'] = [$values['value']];
        }

        if (!is_array($values['value'])){
            throw new \InvalidArgumentException(
                sprintf('@Target expects either a string value, or an array of strings, "%s" given.',
                    is_object($values['value']) ? get_class($values['value']) : gettype($values['value'])
                )
            );
        }

        $bitmask = 0;

        foreach ($values['value'] as $literal) {
            if( ! isset(self::$map[$literal])){
                throw new \InvalidArgumentException(
                    sprintf('Invalid Target "%s". Available targets: [%s]',
                            $literal,  implode(', ', array_keys(self::$map)))
                );
            }

            $bitmask |= self::$map[$literal];
        }

        $this->target = $bitmask;
        $this->value  = $values['value'];
    }

    /**
     * @param int $target
     *
     * @return array
     */
    public static function getNames(int $target) : array
    {
        $names = [];

        if ($target & self::TARGET_CLASS) {
            $names[] = 'CLASS';
        }

        if ($target & self::TARGET_METHOD) {
            $names[] = 'METHOD';
        }

        if ($target & self::TARGET_PROPERTY) {
            $names[] = 'PROPERTY';
        }

        if ($target & self::TARGET_ANNOTATION) {
            $names[] = 'ANNOTATION';
        }

        return $names;
    }
}

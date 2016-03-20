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
 * Docblock var annotation
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 *
 * @Annotation
 */
final class Type
{
    /**
     * Hash-map for handle types declaration.
     *
     * @var array
     */
    private static $typeMap = [
        'float'     => 'double',
        'bool'      => 'boolean',
        // allow uppercase Boolean in honor of George Boole
        'Boolean'   => 'boolean',
        'int'       => 'integer',
    ];

    /**
     * @var string
     */
    public $type = 'mixed';

    /**
     * @var string
     */
    public $arrayType;

    /**
     * Annotation constructor.
     *
     * @param array $values
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        if ( ! isset($values['value'])) {
            return;
        }

        $matches   = null;
        $arrayType = null;
        $type      = trim($values['value']);

        if (isset(self::$typeMap[$type])) {
            $type = self::$typeMap[$type];
        }

        // Checks if the property has array<type>
        if (($pos = strpos($type, '<')) !== false) {
            $arrayType  = substr($type, $pos + 1, -1);
            $type       = 'array';

            if (isset(self::$typeMap[$arrayType])) {
                $arrayType = self::$typeMap[$arrayType];
            }
        }

        // Checks if the property has type[]
        if (($pos = strpos($type, '[')) !== false) {
            $arrayType  = substr($type, 0, $pos);
            $type       = 'array';

            if (isset(self::$typeMap[$arrayType])) {
                $arrayType = self::$typeMap[$arrayType];
            }
        }

        $this->type      = $type;
        $this->arrayType = $arrayType;
    }
}

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

use Reflector;
use ReflectionClass;
use ReflectionProperty;

use Doctrine\Annotations\Context;
use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Annotation\Type;

/**
 * A parser for annotations metadata.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class MetadataParser
{
    /**
     * @var HoaParser
     */
    private $parser;

    /**
     * @var array
     */
    private $imports = [
        'annotation'       => 'Doctrine\Annotations\Annotation',
        'type'             => 'Doctrine\Annotations\Annotation\Type',
        'enum'             => 'Doctrine\Annotations\Annotation\Enum',
        'target'           => 'Doctrine\Annotations\Annotation\Target',
        'ignoreannotation' => 'Doctrine\Annotations\Annotation\IgnoreAnnotation'
    ];

    /**
     * Constructor
     *
     * @param HoaParser $parser
     * @param Resolver  $resolver
     */
    public function __construct(HoaParser $parser, Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->parser   = $parser;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return array
     */
    public function parseAnnotationClass(ReflectionClass $class) : array
    {
        $docblock  = $class->getDocComment();
        $namespace = $class->getNamespaceName();
        $annotations = ($docblock !== false)
            ? $this->parseDockblock($class, $namespace, $docblock)
            : [];

        return $annotations;
    }

   /**
     * @param ReflectionProperty $property
     *
     * @return array
     */
    public function parseAnnotationProperty(ReflectionProperty $property) : array
    {
        $matches     = null;
        $class       = $property->getDeclaringClass();
        $docblock    = $property->getDocComment();
        $namespace   = $class->getNamespaceName();
        $annotations = ($docblock !== false)
            ? $this->parseDockblock($class, $namespace, $docblock)
            : [];

        if ($docblock && preg_match('/@var\s+([^\s]+)/', $docblock, $matches)) {
            $annotations[] = new Type(['value' => $matches[1]]);
        }

        return $annotations;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return array
     */
    private function parseDockblock(Reflector $reflector, string $namespace, string $docblock) : array
    {
        $context = new Context($reflector, $namespace, $this->imports);
        $visitor = new MetadataVisitor($this->resolver, $context);
        $result  = $this->parser->parseDockblock($docblock, $visitor);

        return $result;
    }
}

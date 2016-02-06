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

namespace Doctrine\Annotations;

use ArrayObject;

/**
 *  A list with annotations that are not causing exceptions when not resolved to an annotation class.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class IgnoredAnnotationNames extends ArrayObject
{
    /**
     * The names are case sensitive.
     */
    const DEFAULT_NAMES = [
        // Annotation tags
        'Annotation' => true, 'IgnoreAnnotation' => true,
        /* Can we enable this? 'Enum' => true, */
        'Required' => true, 'Target' => true,
        // Widely used tags (but not existent in phpdoc)
        'fix' => true , 'fixme' => true,
        'override' => true,
        // PHPDocumentor 1 tags
        'abstract'=> true, 'access'=> true,
        'code' => true,
        'deprec'=> true,
        'endcode' => true, 'exception'=> true,
        'final'=> true,
        'ingroup' => true, 'inheritdoc'=> true, 'inheritDoc'=> true,
        'magic' => true,
        'name'=> true,
        'toc' => true, 'tutorial'=> true,
        'private' => true,
        'static'=> true, 'staticvar'=> true, 'staticVar'=> true,
        'throw' => true,
        // PHPDocumentor 2 tags.
        'api' => true, 'author'=> true,
        'category'=> true, 'copyright'=> true,
        'deprecated'=> true,
        'example'=> true,
        'filesource'=> true,
        'global'=> true,
        'ignore'=> true, /* Can we enable this? 'index' => true, */ 'internal'=> true,
        'license'=> true, 'link'=> true,
        'method' => true,
        'package'=> true, 'param'=> true, 'property' => true, 'property-read' => true, 'property-write' => true,
        'return'=> true,
        'see'=> true, 'since'=> true, 'source' => true, 'subpackage'=> true,
        'throws'=> true, 'todo'=> true, 'TODO'=> true,
        'usedby'=> true, 'uses' => true,
        'var'=> true, 'version'=> true,
        // PHPUnit tags
        'codeCoverageIgnore' => true, 'codeCoverageIgnoreStart' => true, 'codeCoverageIgnoreEnd' => true,
        // PHPCheckStyle
        'SuppressWarnings' => true,
        // PHPStorm
        'noinspection' => true,
        // PEAR
        'package_version' => true,
        // PlantUML
        'startuml' => true, 'enduml' => true,
    ];
}

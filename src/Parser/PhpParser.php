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

use SplFileObject;
use ReflectionClass;
use ReflectionFunction;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Kaps <christian.kaps@mohiva.com>
 */
final class PhpParser
{
    /**
     * Parses a class.
     *
     * @param \ReflectionClass $class A <code>ReflectionClass</code> object.
     *
     * @return array A list with use statements in the form (Alias => FQN).
     *
     * @deprecated
     */
    public function parseClass(ReflectionClass $class) : array
    {
        return $this->parse($class);
    }

    /**
     * Parses a function or class.
     *
     * @param \ReflectionClass|\ReflectionFunction $reflection A reflection object.
     *
     * @return array A list with use statements in the form (Alias => FQN).
     *
     * @throws \InvalidArgumentException If reflection class is not an instance of
     *                                   ReflectionClass or ReflectionFunction
     */
    public function parse($reflection) : array
    {
        if ( ! $reflection instanceof ReflectionClass && ! $reflection instanceof ReflectionFunction) {
            throw new \InvalidArgumentException(sprintf(
                'Reflection must be either an instance of ReflectionClass or ReflectionFunction, got %s',
                get_class($reflection)
            ));
        }

        if (method_exists($reflection, 'getUseStatements')) {
            return $reflection->getUseStatements();
        }

        $lineNumber = $reflection->getStartLine();
        $filename   = $reflection->getFilename();
        $content    = ($filename !== false)
            ? $this->getFileContent($filename, $lineNumber)
            : null;

        if ($content === null) {
            return [];
        }

        $namespace = preg_quote($reflection->getNamespaceName());
        $regex     = '/^.*?(\bnamespace\s+' . $namespace . '\s*[;{].*)$/s';
        $content   = preg_replace($regex, '\\1', $content);
        $tokenizer = new TokenParser('<?php ' . $content);

        return $tokenizer->parseUseStatements($reflection->getNamespaceName());
    }

    /**
     * Gets the content of the file right up to the given line number.
     *
     * @param string  $filename   The name of the file to load.
     * @param integer $lineNumber The number of lines to read from file.
     *
     * @return string The content of the file.
     */
    private function getFileContent(string $filename, int $lineNumber)
    {
        if ( ! is_file($filename)) {
            return null;
        }

        $lineCnt = 0;
        $content = '';
        $file    = new SplFileObject($filename);

        while ( ! $file->eof() && $lineCnt++ < $lineNumber) {
            $content .= $file->fgets();
        }

        return $content;
    }
}

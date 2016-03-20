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

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Kaps <christian.kaps@mohiva.com>
 */
class PhpParser
{
    /**
     * Parses a class.
     *
     * @param \ReflectionClass $class A <code>ReflectionClass</code> object.
     *
     * @return array A list with use statements in the form (Alias => FQN).
     */
    public function parseClass(\ReflectionClass $class)
    {
        if (method_exists($class, 'getUseStatements')) {
            return $class->getUseStatements();
        }

        $filename = $class->getFilename();
        $content  = ($filename !== false)
            ? $this->getFileContent($filename, $class->getStartLine())
            : null;

        if ($content === null) {
            return [];
        }

        $namespace = preg_quote($class->getNamespaceName());
        $regex     = '/^.*?(\bnamespace\s+' . $namespace . '\s*[;{].*)$/s';
        $content   = preg_replace($regex, '\\1', $content);
        $tokenizer = new TokenParser('<?php ' . $content);

        return $tokenizer->parseUseStatements($class->getNamespaceName());
    }

    /**
     * Gets the content of the file right up to the given line number.
     *
     * @param string  $filename   The name of the file to load.
     * @param integer $lineNumber The number of lines to read from file.
     *
     * @return string The content of the file.
     */
    private function getFileContent($filename, $lineNumber)
    {
        if ( ! is_file($filename)) {
            return null;
        }

        $lineCnt = 0;
        $content = '';
        $file    = new SplFileObject($filename);

        while ( ! $file->eof()) {
            if ($lineCnt++ == $lineNumber) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }
}

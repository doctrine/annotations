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
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class PhpParser
{
    private $tokens;

    /**
     * Parses a class.
     *
     * @param \ReflectionClass $class
     */
    public function parseClass(\ReflectionClass $class)
    {
        if (false === $filename = $class->getFilename()) {
            return array();
        }
        $src = file_get_contents($filename);
        $name = $class->getName();

        // This is a short-cut for code that follows some conventions:
        // - namespaced
        // - one class per file
        if (preg_match_all('#\bnamespace\s+'.str_replace('\\', '\\\\', $class->getNamespaceName()).'\s*;.*?\b(?:class|interface)\s+'.$class->getShortName().'\b#s', $src, $matches)) {
            foreach ($matches[0] as $match) {
                $classes = $this->parse('<?php '.$match, $name);

                if (isset($classes[$name])) {
                    return $classes[$name];
                }
            }
        }

        $classes = $this->parse($src, $name);

        return $classes[$name];
    }

    private function parse($src, $interestedClass = null)
    {
        $this->tokens = token_get_all($src);
        $classes = $uses = array();
        $namespace = '';
        while ($token = $this->next()) {
            if (T_NAMESPACE === $token[0]) {
                $namespace = $this->parseNamespace();
                $uses = array();
            } elseif (T_CLASS === $token[0] || T_INTERFACE === $token[0]) {
                if ('' !== $namespace) {
                    $class = $namespace.'\\'.$this->nextValue();
                } else {
                    $class = $this->nextValue();
                }
                $classes[$class] = $uses;

                if (null !== $interestedClass && $interestedClass === $class) {
                    return $classes;
                }
            } elseif (T_USE === $token[0]) {
                foreach ($this->parseUseStatement() as $useStatement) {
                    list($alias, $class) = $useStatement;
                    $uses[strtolower($alias)] = $class;
                }
            }
        }

        return $classes;
    }

    private function parseNamespace()
    {
        $namespace = '';
        while ($token = $this->next()) {
            if (T_NS_SEPARATOR === $token[0] || T_STRING === $token[0]) {
                $namespace .= $token[1];
            } elseif (is_string($token) && in_array($token, array(';', '{'))) {
                return $namespace;
            }
        }
    }

    private function parseUseStatement()
    {
        $statements = $class = array();
        $alias = '';
        while ($token = $this->next()) {
            if (T_NS_SEPARATOR === $token[0] || T_STRING === $token[0]) {
                $class[] = $token[1];
            } else if (T_AS === $token[0]) {
                $alias = $this->nextValue();
            } else if (is_string($token)) {
                if (',' === $token || ';' === $token) {
                    $statements[] = array(
                        $alias ? $alias : $class[count($class) - 1],
                        implode('', $class)
                    );
                }

                if (';' === $token) {
                    return $statements;
                }
                if (',' === $token) {
                    $class = array();
                    $alias = '';

                    continue;
                }
            }
        }
    }

    private function next()
    {
        while ($token = array_shift($this->tokens)) {
            if (in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            return $token;
        }
    }

    private function nextValue()
    {
        $token = $this->next();

        return is_array($token) ? $token[1] : $token;
    }
}

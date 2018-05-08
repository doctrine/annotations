<?php

namespace Doctrine\Annotations;

use SplFileObject;

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
     */
    public function parseClass(\ReflectionClass $class)
    {
        if (method_exists($class, 'getUseStatements')) {
            return $class->getUseStatements();
        }

        if (false === $filename = $class->getFileName()) {
            return [];
        }

        $content = $this->getFileContent($filename, $class->getStartLine());

        if (null === $content) {
            return [];
        }

        $namespace = preg_quote($class->getNamespaceName());
        $content = preg_replace('/^.*?(\bnamespace\s+' . $namespace . '\s*[;{].*)$/s', '\\1', $content);
        $tokenizer = new TokenParser('<?php ' . $content);

        $statements = $tokenizer->parseUseStatements($class->getNamespaceName());

        return $statements;
    }

    /**
     * Gets the content of the file right up to the given line number.
     *
     * @param string  $filename   The name of the file to load.
     * @param integer $lineNumber The number of lines to read from file.
     *
     * @return string|null The content of the file or null if the file does not exist.
     */
    private function getFileContent($filename, $lineNumber)
    {
        if ( ! is_file($filename)) {
            return null;
        }

        $content = '';
        $lineCnt = 0;
        $file = new SplFileObject($filename);
        while (!$file->eof()) {
            if ($lineCnt++ == $lineNumber) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }
}

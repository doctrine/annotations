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

namespace Doctrine\Common\Annotations\Cache;

/**
 * File cache for annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class FileCache implements Cache
{
    private $dir;
    private $debug;

    public function __construct($dir, $debug = false)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }
        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $this->dir   = rtrim($dir, '\\/');
        $this->debug = $debug;
    }

    public function getClassAnnotationsFromCache(\ReflectionClass $class)
    {
        $key = $class->getName();

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            @unlink($path);

            return null;
        }

        return include $path;
    }

    public function getPropertyAnnotationsFromCache(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $key = $class->getName().'$'.$property->getName();

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);

            return null;
        }

        return include $path;
    }

    public function getMethodAnnotationsFromCache(\ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $key = $class->getName().'#'.$method->getName();

        $path = $this->dir.'/'.strtr($key, '\\', '-').'.cache.php';
        if (!file_exists($path)) {
            return null;
        }

        if ($this->debug
            && (false !== $filename = $class->getFilename())
            && filemtime($path) < filemtime($filename)) {
            unlink($path);

            return null;
        }

        return include $path;
    }

    public function putClassAnnotationsInCache(\ReflectionClass $class, array $annotations)
    {
        $this->saveCacheFile($this->dir.'/'.strtr($class->getName(), '\\', '-').'.cache.php', $annotations);
    }

    public function putPropertyAnnotationsInCache(\ReflectionProperty $property, array $annotations)
    {
        $key = $property->getDeclaringClass()->getName().'$'.$property->getName();
        $this->saveCacheFile($this->dir.'/'.strtr($key, '\\', '-').'.cache.php', $annotations);
    }

    public function putMethodAnnotationsInCache(\ReflectionMethod $method, array $annotations)
    {
        $key = $method->getDeclaringClass()->getName().'#'.$method->getName();
        $this->saveCacheFile($this->dir.'/'.strtr($key, '\\', '-').'.cache.php', $annotations);
    }

    private function saveCacheFile($path, array $annotations)
    {
        file_put_contents($path, '<?php return unserialize('.var_export(serialize($annotations), true).');');
    }
}
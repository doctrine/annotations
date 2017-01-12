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

use ReflectionFunction;

/**
 * Interface for function annotation readers.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface FunctionReader
{
    /**
     * Gets the annotations applied to a class.
     *
     * @param \ReflectionFunction $function The ReflectionFunction of the function from
     *                                      which the function annotations should be read.
     *
     * @return array An array of Annotations.
     */
    public function getFunctionAnnotations(ReflectionFunction $function) : array;

    /**
     * Gets a class annotation.
     *
     * @param \ReflectionFunction $function       The ReflectionFunction of the function from
     *                                            which the given function annotation should be
     *                                            read.
     * @param string              $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    public function getFunctionAnnotation(ReflectionFunction $function, string $annotationName);
}

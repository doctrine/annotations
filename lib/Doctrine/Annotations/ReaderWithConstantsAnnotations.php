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

namespace Doctrine\Common\Annotations;

/**
 * Interface for annotations readers with constant annotations support.
 *
 * @author Josef Kufner <josef@kufner.cz>
 * @deprecated This interface will be merged into Reader interface and removed in version 2.0.
 */
interface ReaderWithConstantsAnnotations extends Reader
{
    /**
     * Gets the annotations applied to a constant.
     *
     * @param \ReflectionClassConstant $constant The ReflectionClassConstant of the constant
     *                                           from which the annotations should be read.
     *
     * @return array An array of Annotations.
     */
    function getConstantAnnotations(\ReflectionClassConstant $constant);

    /**
     * Gets a constant annotation.
     *
     * @param \ReflectionClassConstant $constant       The ReflectionClassConstant to read the annotations from.
     * @param string                   $annotationName The name of the annotation.
     *
     * @return object|null The Annotation or NULL, if the requested annotation does not exist.
     */
    function getConstantAnnotation(\ReflectionClassConstant $constant, $annotationName);
}

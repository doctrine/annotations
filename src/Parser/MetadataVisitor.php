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

namespace Doctrine\Annotations\Parser;

use Doctrine\Annotations\Exception\ClassNotFoundException;
use Doctrine\Annotations\Reference;
use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Context;

/**
 * A visitor for annotations metadata.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class MetadataVisitor extends BaseVisitor
{
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Resolver $resolver
     * @param Context  $context
     */
    public function __construct(Resolver $resolver, Context $context)
    {
        $this->resolver = $resolver;
        $this->context  = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveClass(string $class) : string
    {
        return $this->resolver->resolve($this->context, $class);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAnnotation(Reference $reference)
    {
        try {
            $fullClass  = $this->resolver->resolve($this->context, $reference->name);
            $isMetadata = strpos($fullClass, 'Doctrine\Annotations\Annotation') === 0;

            if ( ! $isMetadata) {
                return null;
            }

            return new $fullClass($reference->values);
        } catch (ClassNotFoundException $e) {
            return null;
        }
    }
}

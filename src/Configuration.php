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

use Doctrine\Annotations\Metadata\MetadataFactory;
use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Parser\DocParser;
use Doctrine\Annotations\Parser\HoaParser;
use Doctrine\Annotations\Parser\PhpParser;

/**
 * Annotation parser configuration.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class Configuration
{
    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     * class names.
     *
     * @var \Doctrine\Annotations\IgnoredAnnotationNames
     */
    private $ignoredAnnotationNames;

    /**
     * @var \Doctrine\Annotations\Metadata\MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var \Doctrine\Annotations\Parser\MetadataParser
     */
    private $metadataParser;

    /**
     * @var \Doctrine\Annotations\Parser\DocParser
     */
    private $docParser;

    /**
     * @var \Doctrine\Annotations\Parser\PhpParser
     */
    private $phpParser;

    /**
     * @var \Doctrine\Annotations\Parser\HoaParser
     */
    private $hoaParser;

    /**
     * @var \Doctrine\Annotations\Resolver
     */
    private $resolver;

    /**
     * @var \Doctrine\Annotations\Builder
     */
    private $builder;

    /**
     * @param \Doctrine\Annotations\IgnoredAnnotationNames $names
     */
    public function setIgnoredAnnotationNames(IgnoredAnnotationNames $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * @return \Doctrine\Annotations\IgnoredAnnotationNames
     */
    public function getIgnoredAnnotationNames() : IgnoredAnnotationNames
    {
        if ($this->ignoredAnnotationNames !== null) {
            return $this->ignoredAnnotationNames;
        }

        return $this->ignoredAnnotationNames = new IgnoredAnnotationNames(IgnoredAnnotationNames::DEFAULT_NAMES);
    }

    /**
     * @param \Doctrine\Annotations\Metadata\MetadataFactory $factory
     */
    public function setMetadataFactory(MetadataFactory $factory)
    {
        $this->metadataFactory = $factory;
    }

    /**
     * @return \Doctrine\Annotations\Metadata\MetadataFactory
     */
    public function getMetadataFactory() : MetadataFactory
    {
        if ($this->metadataFactory !== null) {
            return $this->metadataFactory;
        }

        return $this->metadataFactory = new MetadataFactory($this->getMetadataParser());
    }

    /**
     * @param \Doctrine\Annotations\Parser\PhpParser $parser
     */
    public function setPhpParser(PhpParser $parser)
    {
        $this->phpParser = $parser;
    }

    /**
     * @return \Doctrine\Annotations\Parser\PhpParser
     */
    public function getPhpParser() : PhpParser
    {
        if ($this->phpParser !== null) {
            return $this->phpParser;
        }

        return $this->phpParser = new PhpParser();
    }

    /**
     * @param \Doctrine\Annotations\Parser\HoaParser $parser
     */
    public function setHoaParser(HoaParser $parser)
    {
        $this->hoaParser = $parser;
    }

    /**
     * @return \Doctrine\Annotations\Parser\HoaParser
     */
    public function getHoaParser() : HoaParser
    {
        if ($this->hoaParser !== null) {
            return $this->hoaParser;
        }

        return $this->hoaParser = new HoaParser();
    }

    /**
     * @param \Doctrine\Annotations\Resolver $resolver
     */
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return \Doctrine\Annotations\Resolver
     */
    public function getResolver() : Resolver
    {
        if ($this->resolver !== null) {
            return $this->resolver;
        }

        return $this->resolver = new Resolver();
    }

    /**
     * @param \Doctrine\Annotations\Builder $builder
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return \Doctrine\Annotations\Builder
     */
    public function getBuilder() : Builder
    {
        if ($this->builder !== null) {
            return $this->builder;
        }

        $resolver = $this->getResolver();
        $factory  = $this->getMetadataFactory();
        $builder  = new Builder($resolver, $factory);

        return $this->builder = $builder;
    }

    /**
     * @param \Doctrine\Annotations\Parser\MetadataParser $parser
     */
    public function setMetadataParser(MetadataParser $parser)
    {
        $this->metadataParser = $parser;
    }

    /**
     * @return \Doctrine\Annotations\Parser\MetadataParser
     */
    public function getMetadataParser() : MetadataParser
    {
        if ($this->metadataParser !== null) {
            return $this->metadataParser;
        }

        $resolver  = $this->getResolver();
        $hoaParser = $this->getHoaParser();
        $parser    = new MetadataParser($hoaParser, $resolver);

        return $this->metadataParser = $parser;
    }

    /**
     * @param \Doctrine\Annotations\Parser\DocParser $parser
     */
    public function setDocParser(DocParser $parser)
    {
        $this->docParser = $parser;
    }

    /**
     * @return \Doctrine\Annotations\Parser\DocParser
     */
    public function getDocParser() : DocParser
    {
        if ($this->docParser !== null) {
            return $this->docParser;
        }

        $builder   = $this->getBuilder();
        $resolver  = $this->getResolver();
        $hoaParser = $this->getHoaParser();
        $parser    = new DocParser($hoaParser, $builder, $resolver);

        return $this->docParser = $parser;
    }
}

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

use Doctrine\Annotations\Context;
use Doctrine\Annotations\Builder;

use Hoa\Compiler\Llk\Llk;
use Hoa\Visitor\Visit;
use Hoa\File\Read;

/**
 * A hoa parser for docblock annotations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class HoaParser
{
    /**
     * @var \Hoa\Compiler\Llk\Parser
     */
    private $parser;

    /**
     * @param string $dockblock
     * @param Visit  $visitor
     *
     * @return mixed
     */
    public function parseDockblock(string $dockblock, Visit $visitor)
    {
        $parser  = $this->getParser();
        $ast     = $parser->parse($dockblock);
        $result  = $visitor->visit($ast);

        return $result;
    }

    /**
     * @param \Hoa\Compiler\Llk\Parser
     */
    private function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return $this->parser = Llk::load(new Read(__DIR__ . '/grammar.pp'));
    }
}

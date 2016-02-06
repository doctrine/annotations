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

namespace Doctrine\Tests\Common\Annotations\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Tests for {@see \Doctrine\Common\Annotations\Annotation\Target}
 *
 * @covers \Doctrine\Common\Annotations\Annotation\Target
 */
class TargetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group DDC-3006
     */
    public function testValidMixedTargets()
    {
        $target = new Target(array("value" => array("ALL")));
        $this->assertEquals(Target::TARGET_ALL, $target->targets);

        $target = new Target(array("value" => array("METHOD", "METHOD")));
        $this->assertEquals(Target::TARGET_METHOD, $target->targets);
        $this->assertNotEquals(Target::TARGET_PROPERTY, $target->targets);

        $target = new Target(array("value" => array("PROPERTY", "METHOD")));
        $this->assertEquals(Target::TARGET_METHOD | Target::TARGET_PROPERTY, $target->targets);
    }
}


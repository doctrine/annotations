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

namespace Doctrine\Common\Annotations\Annotation;


/**
 * Annotation that can be used to signal to the parser 
 * to check the annotation target during the parsing process.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 * 
 * @Annotation
 */
final class Target
{
    const TARGET_CLASS              = 1;
    const TARGET_METHOD             = 2;
    const TARGET_PROPERTY           = 4;
    const TARGET_ANNOTATION         = 8;
    const TARGET_ALL                = 15;
    
    /**
     * @var array
     */
    private static $map = array(
        'ALL'         => self::TARGET_ALL,
        'CLASS'       => self::TARGET_CLASS,
        'METHOD'      => self::TARGET_METHOD,
        'PROPERTY'    => self::TARGET_PROPERTY,
        'ANNOTATION'  => self::TARGET_ANNOTATION,
    );
    
    

    /**
     * @var array
     */
    public $value;
    
    /**
     * Annotation construct
     * 
     * @param array $values 
     */
    public function __construct(array $values)
    {
        if (is_string($values['value'])){
            $values['value'] = array($values['value']);
        }
        if (!is_array($values['value'])){
            throw new \InvalidArgumentException(
                sprintf('@Target expects either a string value, or an array of strings, "%s" given.', 
                    is_object($values['value']) ? get_class($values['value']) : gettype($values['value'])
                )
            );
        }
        $this->value = $values['value'];
    }
    
    /**
     * Returns the allowed usage targets as bitmask.
     *
     * @return integer
     */
    public function getBitmask(){
        $bitmask = 0;
        foreach ($this->value as $literal) {
            if(isset(self::$map[$literal])){
                $bitmask += self::$map[$literal];
            }
        }
        return $bitmask;
    }
    
    /**
     * Returns the literal target declaration.
     *
     * @return string
     */
    public function getLiteral(){
        return implode(', ', $this->value);
    }
}
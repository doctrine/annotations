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

namespace Doctrine\Common\Persistence\Mapping;

/**
 * Contract for a Doctrine persistence layer ClassMetadata class to implement.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.1
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
interface ClassMetadata
{
    /**
     * Gets the mapped identifier field name.
     * 
     * The returned structure is an array of the identifier field names.
     *
     * @return array
     */
    public function getIdentifier();

    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return ReflectionClass
     */
    public function getReflectionClass();

    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isIdentifier($fieldName);

    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @param string $fieldName 
     * @return boolean
     */
    public function hasField($fieldName);

    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasAssociation($fieldName);

    /**
     * Checks if the given field is a mapped single valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isSingleValuedAssociation($fieldName);

    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isCollectionValuedAssociation($fieldName);
    
    /**
     * A numerically indexed list of field names of this persistent class.
     * 
     * This array includes identifier fields if present on this class.
     * 
     * @return array
     */
    public function getFieldNames();
    
    /**
     * A numerically indexed list of association names of this persistent class.
     * 
     * This array includes identifier associations if present on this class.
     * 
     * @return array
     */
    public function getAssociationNames();
    
    /**
     * Returns a type name of this field.
     * 
     * This type names can be implementation specific but should at least include the php types:
     * integer, string, boolean, float/double, datetime.
     * 
     * @param string $fieldName
     * @return string
     */
    public function getTypeOfField($fieldName);
    
    /**
     * Returns the target class name of the given association.
     * 
     * @param string $assocName
     * @return string
     */
    public function getAssociationTargetClass($assocName);
}
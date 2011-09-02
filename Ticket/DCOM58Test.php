<?php
namespace Doctrine\Tests\Common\Annotations\Ticket;

include __DIR__. '/DCOM58Entity.php';

/**
 * @group DCOM58
 */
class DCOM58Test extends \PHPUnit_Framework_TestCase
{
 
    public function testIssue()
    {
        $docblock   = "@Entity";
        $parser     = new \Doctrine\Common\Annotations\DocParser();
        $annots     = $parser->parse($docblock);
        
        $parser->setImports(array(
            'entity'=> __NAMESPACE__ . '\Entity'
        ));
        
        $this->assertEquals(1, count($annots));
        
        var_dump($annots);
    }

}


/**
 * @Annotation
 */
class Entity
{
}
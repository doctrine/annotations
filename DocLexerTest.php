<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\DocLexer;

class DocLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testMarkerAnnotation()
    {
        $lexer = new DocLexer;

        $lexer->setInput("@Name");
        $this->assertNull($lexer->token);
        $this->assertNull($lexer->lookahead);

        $this->assertTrue($lexer->moveNext());
        $this->assertNull($lexer->token);
        $this->assertEquals('@', $lexer->lookahead['value']);

        $this->assertTrue($lexer->moveNext());
        $this->assertEquals('@', $lexer->token['value']);
        $this->assertEquals('Name', $lexer->lookahead['value']);

        $this->assertFalse($lexer->moveNext());
    }
}
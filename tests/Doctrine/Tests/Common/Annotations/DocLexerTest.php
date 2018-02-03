<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\DocLexer;
use PHPUnit\Framework\TestCase;

class DocLexerTest extends TestCase
{
    public function testMarkerAnnotation()
    {
        $lexer = new DocLexer;

        $lexer->setInput('@Name');
        self::assertNull($lexer->token);
        self::assertNull($lexer->lookahead);

        self::assertTrue($lexer->moveNext());
        self::assertNull($lexer->token);
        self::assertEquals('@', $lexer->lookahead['value']);

        self::assertTrue($lexer->moveNext());
        self::assertEquals('@', $lexer->token['value']);
        self::assertEquals('Name', $lexer->lookahead['value']);

        self::assertFalse($lexer->moveNext());
    }

    public function testScannerTokenizesDocBlockWhitConstants()
    {
        $lexer      = new DocLexer();
        $docblock   = '@AnnotationWithConstants(PHP_EOL, ClassWithConstants::SOME_VALUE, ClassWithConstants::CONSTANT_, ClassWithConstants::CONST_ANT3, \Doctrine\Tests\Common\Annotations\Fixtures\InterfaceWithConstants::SOME_VALUE)';

        $tokens =  [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'AnnotationWithConstants',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '(',
                'position'  => 24,
                'type'      => DocLexer::T_OPEN_PARENTHESIS,
            ],
            [
                'value'     => 'PHP_EOL',
                'position'  => 25,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 32,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::SOME_VALUE',
                'position'  => 34,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 64,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::CONSTANT_',
                'position'  => 66,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 95,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => 'ClassWithConstants::CONST_ANT3',
                'position'  => 97,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ',',
                'position'  => 127,
                'type'      => DocLexer::T_COMMA,
            ],
            [
                'value'     => '\\Doctrine\\Tests\\Common\\Annotations\\Fixtures\\InterfaceWithConstants::SOME_VALUE',
                'position'  => 129,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => ')',
                'position'  => 207,
                'type'      => DocLexer::T_CLOSE_PARENTHESIS,
            ]

        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'],     $lookahead['value']);
            self::assertEquals($expected['type'],      $lookahead['type']);
            self::assertEquals($expected['position'],  $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }


    public function testScannerTokenizesDocBlockWhitInvalidIdentifier()
    {
        $lexer      = new DocLexer();
        $docblock   = '@Foo\3.42';

        $tokens =  [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'Foo',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '\\',
                'position'  => 4,
                'type'      => DocLexer::T_NAMESPACE_SEPARATOR,
            ],
            [
                'value'     => 3.42,
                'position'  => 5,
                'type'      => DocLexer::T_FLOAT,
            ]
        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'],     $lookahead['value']);
            self::assertEquals($expected['type'],      $lookahead['type']);
            self::assertEquals($expected['position'],  $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }

    /**
     * @group 44
     */
    public function testWithinDoubleQuotesVeryVeryLongStringWillNotOverflowPregSplitStackLimit()
    {
        $lexer = new DocLexer();

        $lexer->setInput('"' . str_repeat('.', 20240) . '"');

        self::assertInternalType('array', $lexer->glimpse());
    }

    /**
     * @group 44
     */
    public function testRecognizesDoubleQuotesEscapeSequence()
    {
        $lexer    = new DocLexer();
        $docblock = '@Foo("""' . "\n" . '""")';

        $tokens =  [
            [
                'value'     => '@',
                'position'  => 0,
                'type'      => DocLexer::T_AT,
            ],
            [
                'value'     => 'Foo',
                'position'  => 1,
                'type'      => DocLexer::T_IDENTIFIER,
            ],
            [
                'value'     => '(',
                'position'  => 4,
                'type'      => DocLexer::T_OPEN_PARENTHESIS,
            ],
            [
                'value'     => "\"\n\"",
                'position'  => 5,
                'type'      => DocLexer::T_STRING,
            ],
            [
                'value'     => ')',
                'position'  => 12,
                'type'      => DocLexer::T_CLOSE_PARENTHESIS,
            ],
        ];

        $lexer->setInput($docblock);

        foreach ($tokens as $expected) {
            $lexer->moveNext();
            $lookahead = $lexer->lookahead;
            self::assertEquals($expected['value'],    $lookahead['value']);
            self::assertEquals($expected['type'],     $lookahead['type']);
            self::assertEquals($expected['position'], $lookahead['position']);
        }

        self::assertFalse($lexer->moveNext());
    }
}

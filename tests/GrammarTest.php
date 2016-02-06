<?php

namespace Doctrine\AnnotationsTests;

use Hoa\Compiler\Visitor\Dump;
use Doctrine\Annotations\Parser\HoaParser;

class GrammarTest extends TestCase
{
    public function docblockProvider()
    {
        return [

            #####
            [
<<<'DOCBLOCK'
/**
* @Annotation()
*/
DOCBLOCK
,
<<<'DOCBLOCK'
>  #dockblock
>  >  #annotations
>  >  >  #annotation
>  >  >  >  token(annot:identifier, Annotation)

DOCBLOCK
            ],

            #####
            [
<<<'DOCBLOCK'
/**
* @\Ns\Annotation("value")
*/
DOCBLOCK
,
<<<'DOCBLOCK'
>  #dockblock
>  >  #annotations
>  >  >  #annotation
>  >  >  >  token(annot:identifier, \Ns\Annotation)
>  >  >  >  #values
>  >  >  >  >  #value
>  >  >  >  >  >  token(string:string, value)

DOCBLOCK
            ],

            #####
            [
<<<'DOCBLOCK'
/**
* @return array<string>
*/
DOCBLOCK
,
<<<'DOCBLOCK'
>  #dockblock
>  >  #annotations
>  >  >  #annotation
>  >  >  >  token(annot:identifier, return)
>  >  #comments
>  >  >  token(values:text, array<string>)

DOCBLOCK
            ],

            #####
            [
<<<'DOCBLOCK'
/**
* @\Ns\Name(int=1, annot=@Annot, float=1.2)
*/
DOCBLOCK
,
<<<'DOCBLOCK'
>  #dockblock
>  >  #annotations
>  >  >  #annotation
>  >  >  >  token(annot:identifier, \Ns\Name)
>  >  >  >  #values
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, int)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  token(value:number, 1)
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, annot)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  token(annot:identifier, Annot)
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, float)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  token(value:number, 1.2)

DOCBLOCK
            ],

            #####
            [
<<<'DOCBLOCK'
/**
* @Annot(
*  v1={1,2,3},
*  v2={@one,@two,@three},
*  v3={one=1,two=2,three=3},
*  v4={one=@one(1),two=@two(2),three=@three(3)}
* )
*/
DOCBLOCK
,
<<<'DOCBLOCK'
>  #dockblock
>  >  #annotations
>  >  >  #annotation
>  >  >  >  token(annot:identifier, Annot)
>  >  >  >  #values
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, v1)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  #list
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:number, 1)
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:number, 2)
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:number, 3)
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, v2)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  #list
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, one)
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, two)
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, three)
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, v3)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  #map
>  >  >  >  >  >  >  >  >  #pairs
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, one)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 1)
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, two)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 2)
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, three)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 3)
>  >  >  >  >  #value
>  >  >  >  >  >  #pair
>  >  >  >  >  >  >  token(value:identifier, v4)
>  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  #map
>  >  >  >  >  >  >  >  >  #pairs
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, one)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, one)
>  >  >  >  >  >  >  >  >  >  >  >  >  #values
>  >  >  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 1)
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, two)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, two)
>  >  >  >  >  >  >  >  >  >  >  >  >  #values
>  >  >  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 2)
>  >  >  >  >  >  >  >  >  >  #pair
>  >  >  >  >  >  >  >  >  >  >  token(value:identifier, three)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  >  >  token(annot:identifier, three)
>  >  >  >  >  >  >  >  >  >  >  >  >  #values
>  >  >  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  >  >  token(value:number, 3)

DOCBLOCK
            ]
        ];
    }

    /**
     * @dataProvider docblockProvider
     */
    public function testGrammar($docblock, $expected)
    {
        $dump     = new Dump();
        $compiler = new HoaParser();
        $text     = $compiler->parseDockblock($docblock, $dump);

        $this->assertEquals($expected, $text);
    }
}

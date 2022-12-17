# Upgrade from 1.0.x to 2.0.x

`DocLexer::peek()` and `DocLexer::glimpse` now return
`Doctrine\Common\Lexer\Token` objects. When using `doctrine/lexer` 2, these
implement `ArrayAccess` as a way for you to still be able to treat them as
arrays in some ways.

%skip   space               [\x20\x09\x0a\x0d]+
%skip   doc_                [/**]
%skip   _doc                [*/]
%skip   star                [*]

%token  at                  @                           -> annot
%token  text                [^@].*

%token  annot:identifier    [\\a-zA-Z_][\\a-zA-Z0-9_]*  -> values

%skip   values:star         [*]
%skip   values:_doc         [*/]
%skip   values:space        [\x20\x09\x0a\x0d]+
%token  values:comma        ,                           -> value
%token  values:at           @                           -> annot
%token  values:brace_       {                           -> value
%token  values:_brace       }                           -> value
%token  values:parenthesis_ \(                          -> value
%token  values:_parenthesis \)                          -> default
%token  values:text         [^@].*                      -> default

%skip   value:star          [*]
%skip   value:_doc          [*/]
%skip   value:space         [\x20\x09\x0a\x0d]+
%token  value:_parenthesis  \)                          -> values
%token  value:at            @                           -> annot
%token  value:null          null
%token  value:boolean       false|true
%token  value:identifier    [\\a-zA-Z_][\\a-zA-Z0-9_]*
%token  value:brace_        {
%token  value:_brace        }
%token  value:colon         :
%token  value:comma         ,
%token  value:equals        =
%token  value:number        \-?(0|[1-9]\d*)(\.\d+)?([eE][\+\-]?\d+)?

%token  value:quote_        "        -> string
%token  string:string       [^"]+
%token  string:_quote       "        -> value

#dockblock:
    (comments() | annotations())*

#annotations:
    annotation() ( annotation() )*

#annotation:
    ::at:: identifier() ( parameters() | comments() )?

#comments:
    text() ( text() )*

#values:
    value() ( ::comma:: value() )* (::comma::)?

#map:
    ::brace_:: pairs() (::comma::)? ::_brace::

#list:
    ::brace_:: ( (value() ( ::comma:: value() )*) (::comma::)? )? ::_brace::

#pairs:
    pair() ( ::comma:: pair() )*

#pair:
    (identifier() | string() | number() | constant()) ( ::equals:: | ::colon:: ) value()

#value:
    <boolean> | <null> | string() | map() | list() | number() | pair() | annotation() | constant()

parameters:
    ( ::parenthesis_:: ( values() )? ::_parenthesis:: ) | ( string() )?

identifier:
    <identifier>

#constant:
    <identifier> (<colon> <colon> <identifier>)?

string:
    ::quote_:: <string> ::_quote::

quote:
    ::quote_:: ::quote_:: <quote_> <string> <_quote> ::_quote:: ::_quote::

text:
    <text>

number:
    <number>

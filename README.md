# Haijin Parser

The most simple template engine possible, inspired in Ruby's Slim.

[![Latest Stable Version](https://poser.pugx.org/haijin/parser/version)](https://packagist.org/packages/haijin/parser)
[![Latest Unstable Version](https://poser.pugx.org/haijin/parser/v/unstable)](https://packagist.org/packages/haijin/parser)
[![Build Status](https://travis-ci.org/haijin-development/php-parser.svg?branch=master)](https://travis-ci.org/haijin-development/php-parser)
[![License](https://poser.pugx.org/haijin/parser/license)](https://packagist.org/packages/haijin/parser)

### Version 0.0.1

This library is under active development and no stable version was released yet.

If you like it a lot you may contribute by [financing](https://github.com/haijin-development/support-haijin-development) its development.

## Table of contents

1. [Installation](#c-1)
2. [Usage](#c-2)
    1. [Example grammar](#c-2-1)
    2. [Parsing input strings](#c-2-2)
    3. [Grammar components](#c-2-3)
        1. [Expressions](#c-2-3-1)
        2. [Expression matcher](#c-2-3-2)
        3. [Expression handler](#c-2-3-3)
        4. [Symbol particle](#c-2-3-4)
        5. [Regex particle](#c-2-3-5)
        6. [Multiple regex particle](#c-2-3-6)
        7. [String particle](#c-2-3-7)
        8. [Space particle](#c-2-3-8)
        9. [Expression particle](#c-2-3-9)
    4. [Parser methods](#c-2-4)
    5. [Before parsing methods](#c-2-5)
3. [Running the specs](#c-3)

<a name="c-1"></a>
## Installation

Include this library in your project `composer.json` file:

```json
{
    ...

    "require-dev": {
        ...
        "haijin/parser": "^0.0.1",
        ...
    },

    ...
}
```
<a name="c-2"></a>
## Usage

<a name="c-2-1"></a>
### Example grammar

Example of a grammar definition that sums the integers in a literal array:

```php
$parser_definition = new Parser_Definition();
$parser_definition->define( function($parser) {

    $parser->expression( "root",  function() {

        $this->matcher( function() {

            $this->str( "[" ) ->space() ->exp( "integer-list" ) ->space() ->str( "]" );

        });

        $this->handler( function($integers) {

            return array_sum( $integers );

        });

    });

    $parser->expression( "integer-list",  function() {

        $this->matcher( function() {

            $this->exp( "integer" ) ->space() ->str( "," ) ->space() ->exp( "integer-list" )

            ->or()

            ->exp( "integer" );

        });

        $this->handler( function($integer, $list = null) {

            if( $list == null ) {

                return [ $integer ];

            }

            return array_merge( [ $integer ], $list );

        });

    });

    $parser->expression( "integer",  function() {

        $this->matcher( function() {

            $this->regex( "/([0-9]+)/" );

        });

        $this->handler( function($integer_string) {

            return (int) $integer_string;

        });

    });

});
```

It is also possible to define the grammar in a separate file with:

```php
$parser_definition = new Parser_Definition();
$parser_definition->define_in_file( $filename );
```

To see a real use of a complex grammar take a look at the [haijin/haiku](https://github.com/haijin-development/php-haiku) [grammar](https://github.com/haijin-development/php-haiku/blob/master/src/haiku-definition.php).

<a name="c-2-2"></a>
### Parsing input strings

```php
$parser = new Parser( $parser_definition );

$result = $parser->parse_string( "[ 1, 2, 3, 4 ]" );
```

<a name="c-2-3"></a>
### Grammar components

The grammar has only 1 component: `expressions`.

That's the only high level construct the parser needs to parse an input string.

<a name="c-2-3-1"></a>
#### Expressions

An expression has a name and it's defined by one or several sequences of particles.

Simple expression example:

```php
$parser->expression( "integer",  function() {

    $this->matcher( function() {

        $this->regex( "/([0-9]+)/" );

    });

    $this->handler( function($integer_string) {

        return (int) $integer_string;

    });

});
```

The parser begins parsing an input by a single root expression named `root` that must be defined in the grammar:

```php
$parser->expression( "root",  function() {

    $this->matcher( function() {

        $this->str( "[" ) ->space() ->exp( "integer-list" ) ->space() ->str( "]" );

    });

    $this->handler( function($integers) {

        return array_sum( $integers );

    });

});
```


<a name="c-2-3-2"></a>
#### Expression matcher

Each expressions has a `matcher` closure defined. The matcher defines the sequence of particles that defines the expression. That is, the sequence of characters that the parser expects to find when that expression is present in the input.

In it's most simple form, an expression is defined by a single particle.

The parser will match the expression if it matches the particle that defines it:

```php
$parser->expression( "integer",  function() {

    $this->matcher( function() {

        $this->regex( "/([0-9]+)/" );

    });

    $this->handler( function($integer_string) {

        return (int) $integer_string;

    });

});
```

For compound expressions, particles are chained in an ordered sequence of particles.

The parser will match the expression if it matches the whole sequence of particles in the exact order of its definition:

```php
$parser->expression( "addition",  function() {

    $this->matcher( function() {

        $this ->exp( "integer" ) ->space() ->str( "+" ) ->space() ->exp( "integer" );

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

An expression can also be defined by matching the first sequence of particles among several possibilites using the `->or()` statement in its definition:

```php
$parser->expression( "arithmetic-operation",  function() {

    $this->matcher( function() {

        $this ->exp( "addition" )
            ->or()
            ->exp( "substraction" )
            ->or()
            ->exp( "multiplication" )
            ->or()
            ->exp( "division" );

    });

    $this->handler( function($result) {

        return $result;

    });

});
```

With these definitions it is possible to define a BFN grammar in PHP itself.

<a name="c-2-3-3"></a>
#### Expression handler

Each expression has a `handler` closure. The handler closure tells the parser what to do with the particles matched in an expression.

A handler closure has one parameter for each matched particle, and returns a single object as a result.

For instance, in the previous example:

```php
$parser->expression( "integer",  function() {

    $this->matcher( function() {

        $this->regex( "/([0-9]+)/" );

    });

    $this->handler( function($integer_string) {

        return (int) $integer_string;

    });

});
```

the handler takes as its only parameter the matched string with a sequence of digits and converts them to an actual integer.

When the parser matches a sequence of particles, the handler takes a parameter for each matched particle:

```php
$parser->expression( "addition",  function() {

    $this->matcher( function() {

        $this ->exp( "integer" ) ->space() ->str( "+" ) ->space() ->exp( "integer" );

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

In this case the handler takes two parameters, one for the left integer expression and one for the right one, and returns the addition of both integers.

The particles `space()` and `str()` are taken into account to match the expression but do not generante values to the handler and therefore they are not passed as parameters to the handler.
More on that on the particles section.

As it's possible to see in the examples, the particle `exp()` matches a sub-expression also defined in the parser. That expression may be a different one or the same, allowing to define descending recursive grammars:

```php
$parser->expression( "integer-list",  function() {

    $this->matcher( function() {

        $this->exp( "integer" ) ->str( "," ) ->exp( "integer-list" )
        ->or()
        ->exp( "integer" );

    });

    $this->handler( function($integer, $list = null) {

        if( $list == null ) {
            return [ $integer ];
        } else {
            return array_merge( [ $integer ], $list );
        }

    });

});
```

When an `exp` particle is matched, the handler receives as its parameter the result of the sub-expression handler.

This makes processing the parsed input intuitive, effortless and very expressive, with no need to use trees of expressions and visitors.

However, such a tree of expressions can be built quite simply using the expression handlers if needed, but that is entirely optional and is left as a developer's, and not the parsers, design decision.

This parser, and Haijin libraries in general, implements Alan Kays quote:

```
Make simple things simple, make complex things possible.
```

<a name="c-3"></a>
## Running the specs

```
composer specs
```
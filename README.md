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

            $this->lit( "[" ) ->space() ->exp( "integer-list" ) ->space() ->lit( "]" );

        });

        $this->handler( function($integers) {

            return array_sum( $integers );

        });

    });

    $parser->expression( "integer-list",  function() {

        $this->matcher( function() {

            $this->exp( "integer" ) ->space() ->lit( "," ) ->space() ->exp( "integer-list" )

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

To see a real use of a complex grammar take a look at the [haijin/haiku](https://github.com/haijin-development/php-haiku) [grammar](https://github.com/haijin-development/php-haiku/blob/master/src/haiku-definition.php).

<a name="c-2-2"></a>
### Parsing input strings

```php
$parser = new Parser( $parser_definition );

$result = $parser->parse_string( "[ 1, 2, 3, 4 ]" );
```

<a name="c-3"></a>
## Running the specs

```
composer specs
```
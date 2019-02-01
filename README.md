# Haijin Parser

Framework to easily implement recursive descending parsers using a simple and expressive BNF DSL.

[![Latest Stable Version](https://poser.pugx.org/haijin/parser/version)](https://packagist.org/packages/haijin/parser)
[![Latest Unstable Version](https://poser.pugx.org/haijin/parser/v/unstable)](https://packagist.org/packages/haijin/parser)
[![Build Status](https://travis-ci.org/haijin-development/php-parser.svg?branch=master)](https://travis-ci.org/haijin-development/php-parser)
[![License](https://poser.pugx.org/haijin/parser/license)](https://packagist.org/packages/haijin/parser)

### Version 0.1.0

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
    4. [Particles](#c-2-4)
        1. [Symbol particle](#c-2-4-1)
        2. [Regex particle](#c-2-4-2)
        3. [Multiple regex particle](#c-2-4-3)
        4. [String particle](#c-2-4-4)
        5. [Space particle](#c-2-4-5)
        6. [Blank particle](#c-2-4-6)
        7. [Carriage return particle](#c-2-4-7)
        8. [Expression particle](#c-2-4-8)
        9. [Expression processor](#c-2-4-9)
    5. [Parser methods](#c-2-5)
    6. [Before parsing method](#c-2-6)
    7. [Why does the string particle exist](#c-2-7)
3. [Running the specs](#c-3)

<a name="c-1"></a>
## Installation

Include this library in your project `composer.json` file:

```json
{
    ...

    "require-dev": {
        ...
        "haijin/parser": "^0.1.0",
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

            $this->str( "[" ) ->space() ->integer_list() ->space() ->str( "]" );

        });

        $this->handler( function($integers) {

            return array_sum( $integers );

        });

    });

    $parser->expression( "integer_list",  function() {

        $this->matcher( function() {

            $this->integer() ->space() ->str( "," ) ->space() ->integer_list()

            ->or()

            ->integer();

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

A simple expression example:

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

        $this->str( "[" ) ->space() ->integer_list() ->space() ->str( "]" );

    });

    $this->handler( function($integers) {

        return array_sum( $integers );

    });

});
```


<a name="c-2-3-2"></a>
#### Expression matcher

Each expression has a `matcher` closure defined. The matcher defines the sequence of particles that defines the expression. That is, the sequence of characters that the parser expects to find when that expression is present in the input.

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

        $this ->integer() ->space() ->str( "+" ) ->space() ->integer();

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

An expression can also be defined by matching the first sequence of particles among several possibilites using the `->or()` statement in its definition:

```php
$parser->expression( "arithmetic_operation",  function() {

    $this->matcher( function() {

        $this ->addition()
            ->or()
            ->substraction()
            ->or()
            ->multiplication()
            ->or()
            ->division();

    });

    $this->handler( function($result) {

        return $result;

    });

});
```

With these definitions it is possible to define a BNF grammar in PHP itself.

<a name="c-2-3-3"></a>
#### Expression handler

Each expression has a `handler` closure. The handler closure tells the parser what to do with the particles matched in an expression.

A handler closure has one parameter for each matched particle and returns a single object as a result.

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

        $this ->integer() ->space() ->str( "+" ) ->space() ->integer();

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

In this case the handler takes two parameters, one for the left integer expression and one for the right one, and returns the addition of both integers.

The particles `space()` and `str()` are taken into account to match the expression but do not generante values to the handler and therefore they are not passed as parameters to the handler.
More on that on the [particles section](#c-2-4).

As it's possible to see in the examples, the particle `exp()` matches a sub-expression also defined in the grammar. That expression may be a different one or the same, allowing to define descending recursive grammars:

```php
$parser->expression( "integer_list",  function() {

    $this->matcher( function() {

        $this->integer() ->space() ->str( "," ) ->space() ->integer_list()
        ->or()
        ->integer();

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

When an `exp` particle is matched, the `handler` receives the result of the sub-expression `handler` as a parameter.

This makes processing the parsed input intuitive, effortless and very expressive, with no need to use any tree of expressions and visitors.

However, such a tree of expressions can be built quite simply using the expression handlers if needed, but that is entirely optional and is left as a developer's, and not the parsers, design decision for the grammar.

This parser, and Haijin libraries in general, implements Alan Kays principle:

```
Make simple things simple, make complex things possible.
```

<a name="c-2-4"></a>
### Particles

A particle is the smallest building blocks used by the parser to parse an input. A particle matches or not a sequence of characters.

There are different types of particles that combined in sequences define more sophisticated expressions in the grammar.

<a name="c-2-4-1"></a>
#### Symbol particle

`sym` matches a single character or a string and passes the matched string to the `handler`.

Example:

```php
$parser->expression( "additive_operand",  function() {

    $this->matcher( function() {

        $this ->sym( "+" );

    });

    $this->handler( function($symbol) {

    });

});
```

matches the string `"+"` and passes it to the `handler`.

<a name="c-2-4-2"></a>
#### Regex particle

`regex` matches a single group of characters in a regular expression and passes the matched group to the `handler`. A group in a regular expression is the expression defined between `()`.

`regex` particle expects the regex to define only one group, to match more than one group at once use `m_regex` particle instead.

`regex` particle uses [PHP regular expressions](http://php.net/manual/en/function.preg-match.php) to search for patterns.

Example:

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

matches the strings `"1"`, `"123"`, etc, and passes it to the `handler`.

It's a common thing wanting to group other characters in the same regular expression without capturing them. In those cases the following regular expressions patterns are useful:

- group without capturing:  (?:)
- lookahead assertion:      (?=)
- lookahead negation:       (?!)

<a name="c-2-4-3"></a>
#### Multiple regex particle


`m_regex` matches many groups of characters in a single regular expression and passes the matched groups to the `handler`. Each group in a regular expression is the expression defined between `()`.

The difference with `regex` is that `m_regex` expects to match more than one group, so the parameter received by the `handler` is an array of strings containing one string for each matched group, in the same order they were matched in the regular expression.

To match and expect just one string in the `handler` use the `regex` particle instead.

`m_regex` particle uses [PHP regular expressions](http://php.net/manual/en/function.preg-match.php) to search for patterns.

Example:

```php
$parser->expression( "association",  function() {

    $this->matcher( function() {

        $this->regex( "/([0-9a-zA-Z_-\.]+): ([0-9a-zA-Z_-\.]+)/" );

    });

    $this->handler( function($matches) {

        $key = $matches[ 0 ];
        $value = $matches[ 1 ];

        return [ $key => $value ];

    });

});
```

matches the string `"version: 1.0"` and passes it to the `handler` in a single parameter with the value `[ "version", "1.0" ]`.

<a name="c-2-4-4"></a>
#### String particle

`str` matches a single character or a string but does not pass it along to the `handler`.

Example:

```php
$parser->expression( "additive_operand",  function() {

    $this->matcher( function() {

        $this->str( "[" ) ->space() ->literal_list() ->space() ->str( "]" );

    });

    $this->handler( function($list) {

    });

});
```

matches the string `"[ ... ]"` but only passes to the `handler` the value from the sub-expression `literal-list`.


<a name="c-2-4-5"></a>
#### Space particle

`space` particle skips zero or more consecutives spaces and tabs characters in the input until the next non space non tab character. It does not pass any parameters to the handler.

It does not expect any space or tab character to be present, but if they are it skips them.

Use `space` particles to conveniently allow any number of optional spaces and tabs between two other consecutives particles.

Example:

```php
$parser->expression( "addition",  function() {

    $this->matcher( function() {

        $this ->integer() ->space() ->str( "+" ) ->space() ->integer();

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

matches the strings `"3+4"`, `"3 + 4"`, `"3   +    4"`, etc.

<a name="c-2-4-6"></a>
#### Blank particle

`blank` particle skips zero or more consecutives spaces, tabs and carriage returns (`"\n"`) characters in the input until the next non space, non tab, non cr character. It does not pass any parameters to the handler.

It does not expect any space, tab nor cr character to be present, but if they are it skips them.

Use `blank` particles to conveniently allow any number of optional spaces, tabs and crs between two other consecutives particles.

Example:

```php
$parser->expression( "addition",  function() {

    $this->matcher( function() {

        $this ->integer() ->blank() ->str( "+" ) ->blank() ->integer();

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

matches the strings `"3+4"`, `"3 + 4"`, `"3\n+\n4"`, etc.

<a name="c-2-4-7"></a>
#### Carriage return particle

`cr` particle matches a single carriage return (`"\n"`) character. It does not pass any parameters to the handler.

Example:

```php
$parser->expression( "integer_list",  function() {

    $this->matcher( function() {

        $this->integer() ->cr() ->integer_list()
        ->or()
        ->integer();

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

matches the strings `"1"`, `"1\n2"`, `"1\n2\n3"`, etc.

<a name="c-2-4-8"></a>
#### Sub-expression particle

A sub-expression particle matches a sub-expression defined in the same grammar and passes the result of the sub-expression `handler` evaluation to its own handler as the parameter.

To evaluate a sub-expression call the method with the subexpression name.

If the sub-expression is not defined in the grammar the parser will raise an `Haijin\Parser\Expression_Not_Found_Error`.

Example:

```php
$parser->expression( "addition",  function() {

    $this->matcher( function() {

        $this ->integer() ->space() ->str( "+" ) ->space() ->integer();

    });

    $this->handler( function($left_operand, $right_operand) {

        return $left_operand + $right_operand;

    });

});
```

matches the strings `"3 + 4"`.

The sub-expression can be the same expression being defined, allowing to perform a recursive descendent parsing.

Example:

```php
$parser->expression( "literal_array",  function() {

    $this->matcher( function() {

        $this->str( "[" ) ->space() ->literal_list() ->space() ->str( "]" );

    });

    $this->handler( function($values) {

        return array_sum( $values );

    });

});

$parser->expression( "literal_list",  function() {

    $this->matcher( function() {

        $this->literal() ->space() ->str( "," ) ->space() ->literal_list()

        ->or()

        ->literal();

    });

    $this->handler( function($value, $list = null) {

        if( $list == null ) {

            return [ $value ];

        }

        return array_merge( [ $value ], $list );

    });

});

$parser->expression( "literal",  function() {

    $this->matcher( function() {

        $this ->literal_string()
        ->or()
        ->literal_integer()
        ->or()
        ->literal_double()
        ->or()
        ->literal_bool()
        ->or()
        ->literal_null();

    });

    $this->handler( function($value) {

        return $value;

    });

});

// etc
```

matches the strings `"[ true, false, null ]"`, `"[1, "1", 1.0]`", etc.


If for any reason the sub-expression name is not a valid PHP method name, you can call the sub-expression explicitly with `->exp($sub_expression_name)`:

```php
$parser->expression( "literal-array",  function() {

    $this->matcher( function() {

        $this->str( "[" ) ->space() ->exp( "literal-list" ) ->space() ->str( "]" );

    });

    $this->handler( function($values) {

        return array_sum( $values );

    });

});
```

Hoewever defining sub-expressions with `exp` rather than calling the sub-expression as a method is **not** more efficient for the parser. Expression definitions and sequences of expected particles are built during the definition of the grammar given to a `Haijin\Parser\Parser` object, not during the parsing process.

<a name="c-2-4-9"></a>
#### Expression processor

By combining the previous particles it is possible to define sophisticated grammars.

Regular expressions are usually evaluated in an very optimized low level library written in C so they should be quite efficient. Also regular expressions are a well known and standarized language implemented in every single other computer language. There is plenty of documentation and tutorials on regular expressions developers can find.

All these reasons make regular expressions a possible good choice for defining the base particles for a grammar (which are called `tokens` in the programming language parsing literature).

However, expressing some very simple patterns in regular expressions can sometimes be quite difficult and complex. Also, in dispite of being a well known and an accepted standard, the are very unintuitive, they have a huge learning curve, they are not expressive and expressing patterns with regular expressions is very error prone.

The use of the greedy patter `.*` makes writting regular expressions more complex than what it should be. Some simple patterns like capturing a quoted string with escaped characters including quotes can be a nightmare to get it right and to debug it.

On the other hand, thinking how to solve these kind of patterns with procedural processing of a stream using loops and logical conditionals is often quite simple.

So, from a cognitive point of view, the standard and well known regular expressions language can be a bad solution to parse an input stream.

To cope with this lack of expresiveness of the standard regular expressions language, haijin/parser allows to directly parse the input stream in a `processor($closure)` method of an expression but hiding from the developer most of the parsing boiler part related with complex combinations of patterns, like backtracking failed patterns and moving between sequences of patterns.

Example:

```php
$parser->expression( "string_literal",  function() {

    $this->processor( function() {

        $char = $this->next_char();

        // If it does not start with a quote it's not a string literal.
        if( $char != '"' ) {
            return false;
        }


        $literal = "";
        $scaping_next = false;

        while( $this->not_end_of_stream() ) {

            $char = $this->next_char();

            if( $scaping_next === true ) {
                $literal .= $char;

                $scaping_next = false;
                continue;
            }

            if( $char == '\\' ) {
                $scaping_next = true;
                continue;
            }

            // If it is an unescaped quote it is the end of the literal string.
            if( $char == '"' ) {
                break;
            }

            $literal .= $char;
        }

        // Set the parsed string as the result
        $this->set_result( $literal );

        // return true if the particle was a match, false otherwise.
        return true;

    });

    $this->handler( function($string) {

        return $string;

    });

});
```

This example implements parsing a string literal with escaped characters. It's much more verbose than a regular expression but it is also much more expressive, debuggeable and clear.

Expressions defined with `processor` method instead of `match` can be used in other expressions just like any other particle.

When parsing expressions using `processor` the developer must handle the stream correctly.

In order to do that the parser provides the following protocol to parse a particle:

```php
/// Stream methods

/**
 * Returns true if the stream is beyond its last char, false otherwise.
 */
protected function at_end_of_stream();

/**
 * Returns true if the stream has further chars, false otherwise.
 */
protected function not_end_of_stream();

/**
 * Increments by one the input line counter and resets the column counter to 1.
 *
 * Called this method when the parser encounters a "\n" character in order
 * to keep track of the correct line and column indices used in error messages.
 *
 * If this method is not properly called, the parser will still correctly parse
 * valid inputs but the error messages for invalid inputs will be invalid.
 */
public function new_line();

/**
 * Increments the stream pointer and the column counter by $n.
 *
 * Use these method to move backwards or foreward in the stream skipping chars.
 */
public function skip_chars($n);

/**
 * Returns the tail of the stream which has not been parsed yet.
 *
 * Use this method only to debug the parsing process. Using it for the actual
 * parsing of the input will probably be very inneficient.
 */
public function current_string();

/**
 * Returns the current char in the stream and moves forward the stream pointer by one.
 */
public function next_char();

/**
 * Returns the current char in the stream. Does not modify the stream.
 */
public function peek_char();

/**
 * Returns the char at an $offset from its current position. Does not modify the stream.
 */
public function peek_char_at($offset);

/**
 * Sets the result of the particle to be an $object.
 *
 * The result of a particle can be any object, it does not need to be the actual parsed
 * input.
 */
public function set_result($object);

/**
 * Returns the current line index.
 *
 * Use this method for debugging and error messages.
 */
public function current_line();

/**
 * Returns the current column index in the current line.
 *
 * Use this method for debugging and for error messages.
 */
public function current_column();
```

The return value of the `processor` closure must be `true` if the stream completely matched the expression or false otherwise. If the stream did not completely match the expression it's not necesary to restore the stream pointers and indices nor to clean up partial results set for that particle. The parser does the backtraking in the grammar tree and continues to search for a matching expression on the next sequence of particles.


As the grammar DSL is plain PHP code, beside the parser stream protocol it's also possible to call any PHP method and use any PHP feature during the parsing of the input stream, including any string and regex functions, loops, conditionals, etc.


<a name="c-2-5"></a>
### Parser methods

Define methods and call them from within the `handlers` with:

```php
$parser->expression( "literal_list",  function() {

    $this->matcher( function() {

        $this->literal() ->space() ->str( "," ) ->space() ->literal_list()

        ->or()

        ->literal();

    });

    $this->handler( function($value, $list = null) {

        if( $list == null ) {

            return [ $value ];

        }

        return $this->prepend( $value, $list );

    });

});


$parser->def( "prepend", function($item, $array) {

    return array_merge( [ $item ], $array );

});
```

If the method is not present it will raise a `Haijin\Parser\Method_Not_Found_Error`.

<a name="c-2-6"></a>
### Before parsing method

Perform any initialization previous to parsing the input in a `before_parsing` method:

```php
$parser->before_parsing( function() {

    $this->some_configuration_flag = true;

});
```

<a name="c-2-7"></a>
### Why does the string particle exist

What makes a computer language sintax a good sintax?

<a name="c-3"></a>
## Running the specs

```
composer specs
```
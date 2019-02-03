# Haijin Parser

Framework to easily implement recursive descendent parsers using a simple and expressive BNF DSL.

[![Latest Stable Version](https://poser.pugx.org/haijin/parser/version)](https://packagist.org/packages/haijin/parser)
[![Latest Unstable Version](https://poser.pugx.org/haijin/parser/v/unstable)](https://packagist.org/packages/haijin/parser)
[![Build Status](https://travis-ci.org/haijin-development/php-parser.svg?branch=master)](https://travis-ci.org/haijin-development/php-parser)
[![License](https://poser.pugx.org/haijin/parser/license)](https://packagist.org/packages/haijin/parser)

### Version 0.1.1

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
        10. [Optional particles](#c-2-4-10)
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
        "haijin/parser": "^0.1",
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

            $this ->opt( $this->sym( "-" ) ) ->regex( "/([0-9]+)/" );

        });

        $this->handler( function($negative, $integer_string) {

            if( $negative === null ) {
                return (int) $integer_string;
            } else {
                return - (int) $integer_string;
            }

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

As it's possible to see in the examples, the particle `exp()` matches a sub-expression also defined in the grammar. That expression may be a different one or the same, allowing to define recursive descendent grammars:

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

A particle is the smallest building block used by the parser to parse an input. A particle matches or not a sequence of characters.

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

To evaluate a sub-expression call the method with the sub-expression name.

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


If for any reason the sub-expression name is not a valid PHP method name, you can call the sub-expression explicitly with

`->exp($sub_expression_name)`:

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

All these reasons make regular expressions a possible good choice for defining the base particles for a grammar (which are called `tokens` in the programming languages parsers literature).

However, expressing some very simple patterns with regular expressions sintax can sometimes be quite difficult and complex.

The use of the greedy patter `.*` makes writting regular expressions more complex than it should be. Some simple patterns like capturing a quoted string with escaped characters including quotes can be a nightmare to get it right and to debug it.

Postfix notation of `*` and `+` is counter intuitive to all persons. Before having to learn the regex sintax no developer thinks `a sequence 0 or more times`, they think `zero or more times of a sequence`.

Using the same delimiter for grouping without capturing and for captured groups makes it really difficult for developers to parse a regex at a glance.

In dispite of being a well known and an accepted standard, the regex sintax is unintuitive, it has a huge learning curve, it is not expressive and expressing patterns with it is very error prone.

On the other hand, thinking how to solve these kind of patterns with procedural processing of a stream using loops and logical conditionals is often quite simple.

So, from a cognitive point of view, the standard and well known regular expressions sintax can be a bad solution to parse an input stream.

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
 * Call this method when the parser encounters a "\n" character in order
 * to keep track of the correct line and column indices used in the error messages.
 *
 * If this method is not properly called, the parser will still correctly parse
 * valid inputs but the error messages for invalid inputs will be innacurate.
 */
public function new_line();

/**
 * Increments the stream pointer and the column counter by $n.
 *
 * Use these method to move backwards or forwards in the stream skipping chars.
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

The return value of the `processor` closure must be `true` if the stream completely matched the expression or false otherwise. If the stream did not completely match the expression it's not necessary to restore the stream pointers and indices nor to clean up partial results set during that particle. The parser does the backtraking in the grammar tree and continues to search for a matching expression on the next sequence of particles.


As the grammar DSL is plain PHP code, besides the parser stream protocol it's also possible to call any PHP method and use any PHP feature during the parsing of the input stream, including any string and regex functions, third party libraries, loops, conditionals, etc.

<a name="c-2-4-10"></a>
#### Optional particles

`opt` particles makes any particle optional. If the particle is present it is parsed and its value is passed to the `handler`, if it absent it is ignored and a null value is passed to the `handler`.

Example:

```php
$parser->expression( "negative-integer",  function() {

    $this->matcher( function() {

        $this ->opt( $this->sym( "-" ) ) ->integer();

    });

    $this->handler( function($negative, $integer) {

        if( $negative === null ) {
            return (int) $integer;
        } else {
            return - (int) ( $integer );
        }

    });

});
```

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

This whole section is about designing clever, intuitive and expressive languages. If you are more insterested in parsing brackets and semicolons do not read along.

What makes a computer language sintax a good sintax?

The reason regular expressions sintax is so error prone and lacks of expresiveness is that regex sintax was design to match the implementation of low level regular expressions searches for patterns and not the way developers think and express with a minimun effort the patterns they want to find. It's not entirely its designers fault: at the time resources were scarse and the designers could not possible know the result would be so unintuitive and unexpressive.

The same can be said about the original sintax Kernighan & Ritchie designed for the C programming language. Coming from assembler their sintax choice was not bad at all. Actually it was quite good. However Kernighan & Ritchie design choices for the C language were based on making the parsing simple for the parser and not making the expresiveness simple for the developers.

For instance, as other languages like Python, Smalltalk, Ruby and Ecmascript 6 (that is, recent versions of javascript) proved in time, brackets are not necessary for correctly parsing a scope delimiter. Neither is the semicolon to end a statement. It was a choice to make the parser implementation easier, and not the language more expressive.

The same applies for the PHP designer decision of prepending a `$` character to each variable. What was he thinking of to make that symbol mandatory when he designed the most used language in the web? Practically all other existing languajes proved that decision unnecesary.
He was not thinking in making an expressive and intuitive language. He just needed to implement a parser and tagging each variable with a `$` symbol made it easier for him. At the time resources were also scarsed and there was no nice and simple to use grammar domain specific language for PHP to define a complex grammars, so instead of building such a DSL he decided to prepend a dollar sign to every single variable in the language.

So, what parts of a sintax improves the expresiveness of the sintax and what parts makes noise to the developers using that sintax?

Let's take the sintax that express an integer number, for instance. `123`. Each character is a digit that carries an information that if absent would make the parsed input a different one. No digit is optional, except for leading zeros.

Now, if in an expression every symbol is needed to carry a meaning like every digit in a number, why does the particle `str`, that passes no information to the parser handler, exist? If it does carry important information for the parser, where is it left that never arrives to the expression handler? And if `str` symbols do not carry important information for the parser, why would they even exist in the grammar?

There are several reasons for the existence of the `str` particle. That is, for a sintax to include characters that carry no parsing information on themselves.

The first reason, as we saw, is making the parsing easier for computers and more difficult to programmers. That's not a good reason thou.

The second reason is to create a necessary context to correctly parse an input. For instance, if we had a list of many elements

`1, 2, 3`

there is no need of brackets to parse it as a list, but with only one element

`list = [1]`

if the context given by the `[]` was not present it would not be possible to tell if it's  single element or a list with a single item

`list = 1`

In this case the symbols `[]` are necessary and they do carry information for the parser.

That's not the case of the parens in an `if` statement thou. As Python and Ruby sintaxes proved, an `if` statement could be expressed with no need for any brackets:

```c
// Kernighan & Ritchie derived sintaxes

if ( some_condition ) {

}
```

```ruby
# Ruby sintax

if some_condition
end
```

What leads us to the third reason: a decorative reason for the programmers, not the parser.

Why does a computer language, a formal and logical language, would include decorative symbols in its sintax that carry no information to the parser? Because despite they carry no information needed by the parser they do accomplish an important function for the developers using the sintax: the presence of those decorative symbols makes them feel better or worst.

The `str` particle, that defines symbols in the grammar that carry no information for the parser, exists because of decorative reasons to developers.

It's tempting to think that decorative reasons are bad reasons for a particle to exist in a sintax of a formal language. The sintax may end up including all sorts of unnecesary symbols. It may become poetry rather than a programming language. That's a valid concern, but it's also not understanding the value of decorations and underestimating the power that symbols and emotions have in developers, not in computers.

A programming language is something developers choose to use or need to use for one fourth of their day time. As such, it should try to accomplish the following things:

- it should be pleasant for the developers using it
- it should minimize the intelectual effort needed by the developer to read it, understand it and to express hers ideas with it

Those two things, the pleasure or displeasure it produces in developers and the intelectual effort it requires from developers, may be aligned or may be one against the other. But that depends on the sintax, not the developers.

A sintax pleases or unpleases with a certain intensity to the developers using it. The better the sintax is, the more it pleases the developers using it. The worst the sintax is, the more it unpleases the developers.

For instance, regular expressions sintax is extremely unpleasant for developers to read it in part because of the absense of decorative symbols. There are no spaces allowed between consecutive groups, making the interpretation of a long regular expression very unpleasant for persons. A simple decorative space between consecutive groups would make a huge difference on that matter.

In the Kernighan & Ritchie derived sintaxis there's the eternal discusion about the position of the brackets `{}` in different statements. Should the open bracket `{` be in the end of a statement or in a line of its own?

```c
function some_function() {
}

function some_function()
{
}

if ( some_condition ) {
}

if ( some_condition )
{
}
```

Should there be a space between `if` symbol and the opening parens `(` or not? Should statements inside parens be as compact as possible or should there be a space between parens and statements?

```c
if (value < 1)

if( value < 1 )
```

All those decisions about a language sintax are decorative, they do not carry any information needed by the parser, but the do make a huge difference in the developers using it.

What does it mean that a sintax be pleasant to a developer? It means exactly that, but there are some very subtle differences in the way a sintax pleases a developer.

Why did most of the programming languages kept the original Kernighan & Ritchie sintax of brackets and semicolons for more than 4 decades? Because it pleased developers who already knew C, and pretty much every developer who went to a college or university in the past 3 decades learned C as its first programming language because that was the first language taught. Then, when those students got to desing their own languages like Java, Javascript, C++, Golang, etc, they made the choices they felt conformtable with. That pleased them.

However, not all languages choosed the Kernighan & Ritchie sintax. Most notably Smalltalk and Ruby did not. Smalltalk sintax is completely different from C, and yet it's very pleasant for Smalltalk developers.

So, which sintax pleases the more, C or Smalltalk? It's a tricky question.

There are two different reasons for an experience to please a person. Some experiencies please persons with no reasons nor education needed. Those experiencies are pleasant just because. It's easy to identify those experiencies because children like them and enjoy them a lot. For instance, children usually like [Joan Miró](https://en.wikipedia.org/wiki/Joan_Mir%C3%B3) paintings a lot, even if they do not understand the symbolic meanings on them. They like them because of the colours and shapes, because Miró choices of colours and shapes are pleasant to persons with no need of being previously educated.

The same happens with the melodies in the [penthatonic scale](https://en.wikipedia.org/wiki/Pentatonic_scale), children usually enjoy them a lot with no reasons and with no knowlege of music at all. The penthatonic scale pleases persons just because.

On the other hand, other experiencies like drinking alcoholic beverages require an education to get a person to think it pleases her. The same happens with most of classic mussic, absurd humor like Monty Pythons gags, surrealism and both classic and modern art in general.

Althou it may not seem at first glance, there are least two huge differences between pleasant experiencies that requires no education to please and the ones that do required education.

The first one is the big learning curve needed by experiencies that require education to get a person to feel pleased by that experience. On the opposite of a Miró painting, to think that [Marcel Duchamp](https://en.wikipedia.org/wiki/Marcel_Duchamp)'s [fountain](https://en.wikipedia.org/wiki/Fountain_(Duchamp)) pleases us requires 3 years to get a degree on history of arts, or the equivalent of autodidact education. There's nothing in that fountain that could possibly please a person who hasn't been over-exposed to history of art education for years.

The second one is the intelectual effort needed to think the pleasant experience. Listening to melodies in the penthatonic scale does not require much of an intelectual effort to please a person. Listening to [atonal music](https://en.wikipedia.org/wiki/Atonality) requires a huge intelectual effort. It requires an over-exposure to music education. Now, once a person is over-exposed through education it natularizes the intelectual effort. But that doesn't mean that the person effort is lower than before, it just means that the person feels comfortable performing that huge intelectual effort.

So over-exposure to an experience through education makes a person to feel comfortable with that experiencie but it does not lower the intelectual effort that person does when exposed to that experience.

And every effort, even the ones that please us, exhausts us in time. Even some extremely pleasant experiencies that require very little effort like eating, sleeping, spending time with the loved persons, kissing with the girls I'm fond of, exhaust persons and make a person to want to take a break from that experience or to start experimenting a displeasure because of that experience.

Smalltalk and Kernighan & Ritchie sintaxes differ in the over-exposure they require to make a person, not yet a developer, to start pleasing her.

Smalltalk sintax was design to mimic the colloquial language most persons already naturalized way before they start to program. The one every book is written with. It also was design to minimize the sintax decorations used to require the less intelectual effort possible. Because of those design decisions Smalltalk sintax has a very low level learning curve to non programmers who are learning their first computer language.

Kernighan & Ritchie sintax was designed to ease the parsing for the computer and requires quite a learning curve to get a person learning her first programming language to enjoy that sintax. After years of over-exposure to that sintax programmers feel comfortable with it in the same way they feel comfortable with traditions and routines: not because they are any good but because they already know them and feel safe with them, even if they hurt or cause displeasures. Even when that sintax still requires a great and exhausintg intelectual effort for them.

That's the reason most C programmers find Smalltalk sintax odd: they feel afraid of it in the same way a child is afraid the first day at a new school, but it has nothing to do with the sintax itself.

Back to the question of which sintax is better, Smalltalk or Kernighan & Ritchie's, now we are able to sketch an answer.

Smalltalk has a lower learning curve for a person learning her first programming language, and once she learned it it requires a lower intelectual effort because the sintax matches the colloquial writtings.

Kernighan & Ritchie has a greater learning curve for a person learning her first programming language and once she learned it requires a considerable intelectual effort that exhauts the person even with the safety feelling it produces in her.

That doesn't mean that Smalltalks sintax is the best a programming language can define. Ruby language designer's decision to drop the `self` particle as mandatory is a great design decision that adds a lot of expressiveness to the sintax and, at the same time, reduces the intelectual effort needed to read it. Ruby's community is known for having created the most expressive DSLs ever, in any language. That is in part because of Ruby desinger realized that the `self` statement was merly a decoratiion and made it optional.

There is a fourth reason for the presence of decorative symbols in a sintax: cognitive reasons to reduce the intelectual effort needed to interpret it.

The use of decorative symbols can increse or reduce a lot the intelectual effort done by a developer to read an expression.

For instance, the expressions

```c
if (value < 1)
```

and

```c
if( value < 1 )
```

only differ in a few spaces between other symbols. And yet those spaces make a difference.

In the first case they send the readers attention to the `if` symbol at the left, in the second case they send the readers attention to what's inside the parens. It may not seem like a big difference, but in a regular computer program there are hundreds of thousands of `if` statments, and this little difference has an impact on the exhaustion the reader experiments.

And what does a developer wants to understand when reading an `if` statement? The logical condition, not the decorative symbols around it. A clever design for a computer language sintax is not about aesthetic decisions nor keeping traditions but to use decorations to emphasize what developers want to read to reduce their intelectual efforts, exhaust them less and please them more.

Another example is the use of brackets `{}` in functions definitions and `statements`.

```c
function some_function() {
    print(123);
}
```

```c
function some_function()
{
    print( 123 );
}
```

in the first case the cognitive emphasis goes to the function signature declaration, in the second case it creates the cognitive idea that the reader is watching something composed by a header and a body, where no one has more importance than the other. It produces a balance bewteen the both of them.


Spaces inside parens and brackets makes the statements inside them far more easy to read and leads the readers attention to the statements rather than the function name.

```c
print(123,"123",123);
```

```c
print( 123, "123", "123" );
```

Why would it be better to emphazise the statements inside a function call rather than the function name? Because the parameters of a function call is where the program will have most of its errors, and not calling the wrong function.


Sometimes decorations have no other reason that to please the reader:

```c
while( value < constant ) {
    print(123);

    print(123);
}
```

The opening `{` goes in the same line to emphazise the loop condition. It leads the balance to the `while` statement.

But the first stamement closed to the emphazised `while` produces a little annoyment when reading it.

```c
while( value < constant ) {

    print(123);

    print(123);
}
```

In this case the first statement inside the loop body is read without the annoyment, but the last statement produces an annoyment because it breaks the vertical symmetry within the loop body.

```c
while( value < constant ) {

    print(123);

    print(123);

}
```

In this case the symmetry produces a pleasant feeling, or at least avoids the unpleaseant feeling caused by the previous asymmetry.


In summary, in Haijin Development we go further with Alan Kay's principle and define our own principle:

```
Simple things should be effortless,
complex things should be possible,
all things should be pleasant
with the minimun amount of education.
```

That's my principle. Unlike Groucho Marx, I do not have others.

<a name="c-3"></a>
## Running the specs

```
composer specs
```
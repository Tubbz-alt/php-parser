<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching an eos particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this ->eos();

                    });

                    $this->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->it( "with an empty string the expresion is valid", function() {

            $result = $this->parser->parse_string( "" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "with a non empty string the expresion is invalid", function() {

            $this->expect(function () {

                $this->parser->parse_string( "123" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "123". At line: 1 column: 1.'
                    );
            }); 

        });

    });

    $this->describe( "at the end of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this ->str( "123" ) ->eos();

                    });

                    $this->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->it( "with a matching string the expresion is valid", function() {

            $result = $this->parser->parse_string( "123" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "fails with a char", function() {

            $this->expect(function () {

                $this->parser->parse_string( "1234" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "4". At line: 1 column: 4.'
                    );
            }); 

        });

        $this->it( "fails with a space", function() {

            $this->expect(function () {

                $this->parser->parse_string( "123 " );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression " ". At line: 1 column: 4.'
                    );
            }); 

        });

        $this->it( "fails with a cr", function() {

            $this->expect(function () {

                $this->parser->parse_string( "123\n" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected "\n". At line: 1 column: 4.'
                    );
            }); 

        });

    });

});
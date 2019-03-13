<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;
use Haijin\Parser\Errors\Unexpected_Expression_Error;

$spec->describe( "When matching an eol particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->eol() ->str( "123" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->it( "passes with a valid expression", function() {

            $result = $this->parser->parse_string( "\n123" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "fails with an invalid expression", function() {

            $this->expect(function () {

                $this->parser->parse_string( "" );

            }) ->to() ->raise(
                Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected end of stream. At line: 1 column: 1.'
                    );
            }); 

        });

    });

    $this->describe( "in the middle of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "1" ) ->eol() ->str( "2" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->it( "passes with a valid expression", function() {

            $result = $this->parser->parse_string( "1\n2" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "fails with an invalid expression", function() {

            $this->expect(function () {

                $this->parser->parse_string( "1 2" );

            }) ->to() ->raise(
                Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression " 2". At line: 1 column: 2.'
                    );
            }); 

        });

    });

    $this->describe( "at the end of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "123" ) ->eol();

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->it( "with a cr the expresion is valid", function() {

            $result = $this->parser->parse_string( "123\n" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "with an eos the expresion is valid", function() {

            $result = $this->parser->parse_string( "123" );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

        $this->it( "with a non matching string the expresion is invalid", function() {

            $this->expect(function () {

                $this->parser->parse_string( "123 " );

            }) ->to() ->raise(
                Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression " ". At line: 1 column: 4.'
                    );
            }); 

        });

    });

});
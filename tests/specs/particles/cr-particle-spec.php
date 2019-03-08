<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a cr particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->cr() ->str( "123" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "when it is present", function() {

            $this->let( "input", function() {
                return "\n123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "when it is absent", function() {

            $this->let( "input", function() {
                return "123";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    \Haijin\Parser\Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "123". At line: 1 column: 1.'
                        );
                }); 

            });

        });

    });

    $this->describe( "in the middle of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "1" ) ->cr() ->str( "2" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "when it is present", function() {

            $this->let( "input", function() {
                return "1\n2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "when it is absent", function() {

            $this->let( "input", function() {
                return "1 2";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    \Haijin\Parser\Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression " 2". At line: 1 column: 2.'
                        );
                }); 

            });

        });

    });

    $this->describe( "at the end of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "123" ) ->cr();

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "when it is present", function() {

            $this->let( "input", function() {
                return "123\n";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "when it is absent", function() {

            $this->let( "input", function() {
                return "123";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    \Haijin\Parser\Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected end of stream. At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

});
<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a space particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function() {

                $this->matcher( function() {

                    $this ->str( "1" ) ->space() ->str( "2" );

                });

                $this->handler( function() {
                    return "parsed";
                });

            });

        });

    });

    $this->describe( "with no spaces", function() {

        $this->let( "input", function() {
            return "12";
        });

        $this->it( "the expresion is valid", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

    });


    $this->describe( "with a space", function() {

        $this->let( "input", function() {
            return "1 2";
        });

        $this->it( "the expresion is valid", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

    });

    $this->describe( "with spaces", function() {

        $this->let( "input", function() {
            return "1   2";
        });

        $this->it( "the expresion is valid", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

    });

    $this->describe( "with a tab", function() {

        $this->let( "input", function() {
            return "1\t2";
        });

        $this->it( "the expresion is valid", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

    });

    $this->describe( "with tabs", function() {

        $this->let( "input", function() {
            return "1\t\t2";
        });

        $this->it( "the expresion is valid", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "parsed" );

        });

    });

    $this->describe( "for an unexpected expression at the beginning", function() {

        $this->let( "input", function() {
            return " 12";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression " 12". At line: 1 column: 1.'
                    );
            }); 

        });

    });

    $this->describe( "for an unexpected expression after an expected expression", function() {

        $this->let( "input", function() {
            return "12 ";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression " ". At line: 1 column: 3.'
                    );
            }); 

        });

    });

    $this->describe( "with a cr", function() {

        $this->let( "input", function() {
            return "1\n2";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "". At line: 1 column: 2.'
                    );
            }); 

        });

    });

});
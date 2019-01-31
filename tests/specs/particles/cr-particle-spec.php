<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a cr particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function() {

                $this->matcher( function() {

                    $this ->str( "1" ) ->cr() ->str( "2" );

                });

                $this->handler( function() {
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
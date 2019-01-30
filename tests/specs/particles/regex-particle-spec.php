<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a regex particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function() {

                $this->matcher( function() {

                    $this->regex( "/([0-9]+)/" );

                });

                $this->handler( function($integer_string) {

                    return (int) $integer_string;

                });

            });

        });

    });

    $this->describe( "for each matched expression found", function() {

        $this->let( "input", function() {
            return "123";
        });

        $this->it( "evaluates the handler closure", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

        });

    });


    $this->describe( "for an unexpected expression at the beginning", function() {

        $this->let( "input", function() {
            return "a123";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "a123". At line: 1 column: 1.'
                    );
            }); 

        });

    });

    $this->describe( "for an unexpected expression after an expected expression", function() {

        $this->let( "input", function() {
            return "123a";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "a". At line: 1 column: 4.'
                    );
            }); 

        });

    });

});
<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a particle among several", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->integer() ->or() ->alpha() ->or() ->str( "#" );

                });

                $exp->handler( function($integer_or_alpha = null) {

                    return $integer_or_alpha;

                });

            });

            $parser->expression( "integer",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->regex( "/([0-9]+)/" );

                });

                $exp->handler( function($integer_string) {

                    return (int) $integer_string;

                });

            });

            $parser->expression( "alpha",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->regex( "/([a-z]+)/" );

                });

                $exp->handler( function($alpha_string) {

                    return $alpha_string;

                });

            });

        });

    });

    $this->describe( "when the input matches the first expression", function() {

        $this->let( "input", function() {
            return "123";
        });

        $this->it( "evaluates the handler closure", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

        });

    });

    $this->describe( "when the input matches the second expression", function() {

        $this->let( "input", function() {
            return "abc";
        });

        $this->it( "evaluates the handler closure", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "abc" );

        });

    });

    $this->describe( "when the input matches the third expression", function() {

        $this->let( "input", function() {
            return "#";
        });

        $this->it( "evaluates the handler closure", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->be() ->null();

        });

    });

    $this->describe( "for an unexpected expression at the beginning", function() {

        $this->let( "input", function() {
            return "+123";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "+123". At line: 1 column: 1.'
                    );
            }); 

        });

    });

    $this->describe( "for an unexpected expression after an expected expression", function() {

        $this->let( "input", function() {
            return "123+";
        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( $this->input );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "+". At line: 1 column: 4.'
                    );
            }); 

        });

    });

});
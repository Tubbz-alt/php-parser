<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When an expression does not define a handler", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root", function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->with_handler() ->no_handler();

                });

                $exp->handler( function($value) {

                    return $value;

                });

            });

            $parser->expression( "with_handler", function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->str( "1" );

                });

                $exp->handler( function() {

                    return "parsed";

                });

            });

            $parser->expression( "no_handler", function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->str( "2" );

                });

            });

        });

    });

    $this->let( "input", function() {
        return "12";
    });

    $this->it( "no handler is evaluated for that expression", function() {

        $result = $this->parser->parse_string( $this->input );

        $this->expect( $result ) ->to() ->equal( "parsed" );

    });

});
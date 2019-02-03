<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching an optional particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function() {

                $this->matcher( function() {

                    $this
                        ->str( "1" ) ->opt( $this->sym( "2" ) ) ->str( "3" );

                });

                $this->handler( function($string) {
                    return $string;
                });

            });

        });

    });

    $this->describe( "when the optional particle is present", function() {

        $this->let( "input", function() {
            return "123";
        });

        $this->it( "evaluates the handler with its value", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( "2" );

        });

    });

    $this->describe( "when the optional particle is absent", function() {

        $this->let( "input", function() {
            return "13";
        });

        $this->it( "evaluates the handler with its value", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->be() ->null();

        });

    });

});
<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a procedural particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->expression( "root",  function() {

                $this->matcher( function() {

                    $this ->p();

                });

                $this->handler( function($string) {
                    return $string;
                });

            });

            $parser->expression( "p",  function() {

                $this->processor( function() {

                    if( $this->peek_char() == "#" ) {

                        $this->set_result( "#" );

                        $this->next_char();

                        return true;

                    }

                    return false;

                });

                $this->handler( function($string) {
                    return $string;
                });

            });

        });

    });

    $this->let( "input", function() {
        return "#";
    });

    $this->it( "parses the input stream", function() {

        $result = $this->parser->parse_string( $this->input );

        $this->expect( $result ) ->to() ->equal( "#" );

    });

});
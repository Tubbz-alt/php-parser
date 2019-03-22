<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;
use Haijin\Parser\Errors\Unexpected_Expression_Error;
use Haijin\Errors\File_Not_Found_Error;

$spec->describe( "When matching recursive expressions", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->let( "parser_definition", function() {

        return ( new Parser_Definition() )->define( function($parser) {

            $parser->def( 'custom_method', new Custom_Method() );

            $parser->expression( "root",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->str( "[" ) ->space() ->integer_list() ->space() ->str( "]" );

                });

                $exp->handler( function($integers) {

                    return array_sum( $integers ) + $this->custom_method( 3, 4 );

                });

            });

            $parser->expression( "integer_list",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->integer() ->space() ->str( "," ) ->space() ->integer_list()

                    ->or()

                    ->integer();

                });

                $exp->handler( new Integer_List_Handler() );

            });

            $parser->expression( "integer",  function($exp) {

                $exp->matcher( function($exp) {

                    $exp ->opt( $exp->sym( "-" ) ) ->regex( "/([0-9]+)/" );

                });

                $exp->handler( new Integer_Handler() );

            });

        });

    });

    $this->describe( "when the input matches a recursive expression", function() {

        $this->let( "input", function() {
            return "[1, 2, 3, 4]";
        });

        $this->it( "evaluates the handler closure", function() {

            $result = $this->parser->parse_string( $this->input );

            $this->expect( $result ) ->to() ->equal( 17 );

        });

    });

});

class Integer_List_Handler
{
    public function __invoke($integer, $list = null)
    {
        if( $list === null ) {

            return [ $integer ];

        }

        return array_merge( [ $integer ], $list );
    }
}

class Integer_Handler
{
    public function __invoke($negative, $integer_string)
    {
        if( $negative === null ) {
            return (int) $integer_string;
        } else {
            return - (int) $integer_string;
        }
    }
}

class Custom_Method
{
    public function __invoke($n, $m)
    {
        return $n + $m;
    }
}
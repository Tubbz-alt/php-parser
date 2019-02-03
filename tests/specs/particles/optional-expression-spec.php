<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching an optional particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this
                            ->opt( $this->integer() ) ->str( "a" ) ->str( "b" );

                    });

                    $this->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "integer",  function() {

                    $this->matcher( function() {

                        $this->regex( "/([0-9]+)/" );

                    });

                    $this->handler( function($string) {
                        return (int) $string;
                    });

                });

            });

        });

        $this->describe( "when the optional particle is present", function() {

            $this->let( "input", function() {
                return "1ab";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( 1 );

            });

        });

        $this->describe( "when the optional particle is absent", function() {

            $this->let( "input", function() {
                return "ab";
            });

            $this->it( "evaluates the handler with null", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be() ->null();

            });

        });

    });

    $this->describe( "in the middle of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this
                            ->str( "a" ) ->opt( $this->integer() ) ->str( "b" );

                    });

                    $this->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "integer",  function() {

                    $this->matcher( function() {

                        $this->regex( "/([0-9]+)/" );

                    });

                    $this->handler( function($string) {
                        return (int) $string;
                    });

                });

            });

        });

        $this->describe( "when the optional particle is present", function() {

            $this->let( "input", function() {
                return "a2b";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( 2 );

            });

        });

        $this->describe( "when the optional particle is absent", function() {

            $this->let( "input", function() {
                return "ab";
            });

            $this->it( "evaluates the handler with null", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be() ->null();

            });

        });

    });

    $this->describe( "at the end of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this
                            ->str( "a" ) ->str( "b" ) ->opt( $this->integer() );

                    });

                    $this->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "integer",  function() {

                    $this->matcher( function() {

                        $this->regex( "/([0-9]+)/" );

                    });

                    $this->handler( function($string) {
                        return (int) $string;
                    });

                });

            });

        });

        $this->describe( "when the optional particle is present", function() {

            $this->let( "input", function() {
                return "ab3";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "3" );

            });

        });

        $this->describe( "when the optional particle is absent", function() {

            $this->let( "input", function() {
                return "ab";
            });

            $this->it( "evaluates the handler with null", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be() ->null();

            });

        });

    });

    $this->describe( "as a single expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this
                            ->opt( $this->integer() );

                    });

                    $this->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "integer",  function() {

                    $this->matcher( function() {

                        $this->regex( "/([0-9]+)/" );

                    });

                    $this->handler( function($string) {
                        return (int) $string;
                    });

                });

            });

        });

        $this->describe( "when the optional particle is present", function() {

            $this->let( "input", function() {
                return "1";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "1" );

            });

        });

        $this->describe( "when the optional particle is absent", function() {

            $this->let( "input", function() {
                return "";
            });

            $this->it( "evaluates the handler with null", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be() ->null();

            });

        });

    });

    $this->describe( "as consecutives expressions", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this
                            ->opt( $this->integer() )
                            ->opt( $this->str( " " ) )
                            ->opt( $this->integer() );

                    });

                    $this->handler( function($int_1, $int_2) {
                        return [ $int_1, $int_2 ];
                    });

                });

                $parser->expression( "integer",  function() {

                    $this->matcher( function() {

                        $this->regex( "/([0-9]+)/" );

                    });

                    $this->handler( function($string) {
                        return (int) $string;
                    });

                });

            });

        });

        $this->describe( "when both optional particles are present", function() {

            $this->let( "input", function() {
                return "1 2";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( [ 1, 2 ] );

            });

        });

        $this->describe( "when the first optional particle is present", function() {

            $this->let( "input", function() {
                return "1";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( [ 1, null ] );

            });

        });

        $this->describe( "when the second optional particle is present", function() {

            $this->let( "input", function() {
                return " 2";
            });

            $this->it( "evaluates the handler with its value", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( [ null, 2 ] );

            });

        });

        $this->describe( "when both particles are absent", function() {

            $this->let( "input", function() {
                return "";
            });

            $this->it( "evaluates the handler with null", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( [ null, null ] );

            });

        });

    });

});
<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;
use Haijin\Parser\Errors\Unexpected_Expression_Error;
use Haijin\Parser\Errors\Expression_Not_Found_Error;


$spec->describe( "When matching an expression particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of a expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->integer() ->str( "abc" );

                    });

                    $exp->handler( function($integer) {

                        return $integer;

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

            });

        });

        $this->it( "passes for a valid expression", function() {

            $result = $this->parser->parse_string( "123abc" );

            $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

        });

        $this->describe( "fails if the sub-expression does not match", function() {

            $this->let( "input", function() {
                return "abcz";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "abcz". At line: 1 column: 1.'
                        );
                }); 

            });

        });

        $this->describe( "fails if the following particle does not match", function() {

            $this->let( "input", function() {
                return "123a";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "a". At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

    $this->describe( "in the middle of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "abc" ) ->integer() ->str( "cba" ); 

                    });

                    $exp->handler( function($integer) {

                        return $integer;

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

            });

        });

        $this->it( "passes for a valid expression", function() {

            $result = $this->parser->parse_string( "abc123cba" );

            $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

        });

        $this->describe( "fails if the sub-expression does not match", function() {

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( "abczabc" );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "zabc". At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

    $this->describe( "at the end of a expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "abc" ) ->integer();

                    });

                    $exp->handler( function($integer) {

                        return $integer;

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

            });

        });

        $this->it( "passes for a valid expression", function() {

            $result = $this->parser->parse_string( "abc123" );

            $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

        });

        $this->describe( "fails if the sub-expression does not match", function() {

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( "zabc" );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "zabc". At line: 1 column: 1.'
                        );
                }); 

            });

        });

    });

    $this->describe( "that is not defined", function() {

        $this->let( "parser_definition", function() {

            return new Parser_Definition();

        });

        $this->it( "raises an error", function() {

            $this->expect( function() {

                $this->parser->parse_string( "123abc" );

            }) ->to() ->raise(
                Expression_Not_Found_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'The expression "root" was not found in this parser.'
                    );
            });

        });

    });

});
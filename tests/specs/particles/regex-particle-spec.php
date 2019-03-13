<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;
use Haijin\Parser\Errors\Unexpected_Expression_Error;

$spec->describe( "When matching a regex particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->regex( "/([0-9]+)/" ) ->str( "abc" );

                    });

                    $exp->handler( function($string) {

                        return (int) $string;

                    });

                });

            });

        });

        $this->describe( "for each matched expression found", function() {

            $this->let( "input", function() {
                return "123abc";
            });

            $this->it( "evaluates the handler closure", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

            });

        });


        $this->describe( "for an unexpected expression at the beginning", function() {

            $this->let( "input", function() {
                return "z123";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "z123". At line: 1 column: 1.'
                        );
                }); 

            });

        });

        $this->describe( "for an unexpected expression at following particle", function() {

            $this->let( "input", function() {
                return "123z";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "z". At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

    $this->describe( "in the midlle of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "abc" ) ->regex( "/([0-9]+)/" ) ->str( "cba" );

                    });

                    $exp->handler( function($string) {

                        return (int) $string;

                    });

                });

            });

        });

        $this->describe( "for each matched expression found", function() {

            $this->let( "input", function() {
                return "abc123cba";
            });

            $this->it( "evaluates the handler closure", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

            });

        });


        $this->describe( "for an unexpected expression at the beginning", function() {

            $this->let( "input", function() {
                return "abczcba";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "zcba". At line: 1 column: 4.'
                        );
                }); 

            });

        });

        $this->describe( "for an unexpected expression at following particle", function() {

            $this->let( "input", function() {
                return "abc123z";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected expression "z". At line: 1 column: 7.'
                        );
                }); 

            });

        });

    });

    $this->describe( "at the end of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "abc" ) ->regex( "/([0-9]+)/" );

                    });

                    $exp->handler( function($string) {

                        return (int) $string;

                    });

                });

            });

        });

        $this->describe( "for each matched expression found", function() {

            $this->let( "input", function() {
                return "abc123";
            });

            $this->it( "evaluates the handler closure", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->be( "===" ) ->than( 123 );

            });

        });


        $this->describe( "for an unexpected expression", function() {

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
                            'Unexpected expression "z". At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

});
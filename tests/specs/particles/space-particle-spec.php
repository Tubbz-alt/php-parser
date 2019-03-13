<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;
use Haijin\Parser\Errors\Unexpected_Expression_Error;

$spec->describe( "When matching a space particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->space() ->str( "123" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "with no spaces", function() {

            $this->let( "input", function() {
                return "123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });


        $this->describe( "with a space", function() {

            $this->let( "input", function() {
                return " 123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with spaces", function() {

            $this->let( "input", function() {
                return "   123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a tab", function() {

            $this->let( "input", function() {
                return "\t123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with tabs", function() {

            $this->let( "input", function() {
                return "\t\t123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a cr", function() {

            $this->let( "input", function() {
                return "\n123";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected "\n". At line: 1 column: 1.'
                        );
                }); 

            });

        });

    });

    $this->describe( "in the middle of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "1" ) ->space() ->str( "2" );

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "with no spaces", function() {

            $this->let( "input", function() {
                return "12";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });


        $this->describe( "with a space", function() {

            $this->let( "input", function() {
                return "1 2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with spaces", function() {

            $this->let( "input", function() {
                return "1   2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a tab", function() {

            $this->let( "input", function() {
                return "1\t2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with tabs", function() {

            $this->let( "input", function() {
                return "1\t\t2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a cr", function() {

            $this->let( "input", function() {
                return "1\n2";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected "\n". At line: 1 column: 2.'
                        );
                }); 

            });

        });

    });

    $this->describe( "at the end of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "123" ) ->space();

                    });

                    $exp->handler( function() {
                        return "parsed";
                    });

                });

            });

        });

        $this->describe( "with no spaces", function() {

            $this->let( "input", function() {
                return "123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });


        $this->describe( "with a space", function() {

            $this->let( "input", function() {
                return "123 ";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with spaces", function() {

            $this->let( "input", function() {
                return "123   ";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a tab", function() {

            $this->let( "input", function() {
                return "123\t";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with tabs", function() {

            $this->let( "input", function() {
                return "123\t\t";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with a cr", function() {

            $this->let( "input", function() {
                return "123\n";
            });

            $this->it( "raises an error", function() {

                $this->expect( function() {

                    $this->parser->parse_string( $this->input );

                }) ->to() ->raise(
                    Unexpected_Expression_Error::class,
                    function($error) {

                        $this->expect( $error->getMessage() ) ->to() ->equal(
                            'Unexpected "\n". At line: 1 column: 4.'
                        );
                }); 

            });

        });

    });

});
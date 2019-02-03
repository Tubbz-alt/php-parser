<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a blank particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the beginning of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this ->blank() ->str( "123" );

                    });

                    $this->handler( function() {
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

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with crs", function() {

            $this->let( "input", function() {
                return "\n \t\n123";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

    });

    $this->describe( "in the middle of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this ->str( "1" ) ->blank() ->str( "2" );

                    });

                    $this->handler( function() {
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

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with crs", function() {

            $this->let( "input", function() {
                return "1\n \t\n2";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

    });

    $this->describe( "at the end of a line", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function() {

                    $this->matcher( function() {

                        $this ->str( "123" ) ->blank();

                    });

                    $this->handler( function() {
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

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

        $this->describe( "with crs", function() {

            $this->let( "input", function() {
                return "123\n \t\n";
            });

            $this->it( "the expresion is valid", function() {

                $result = $this->parser->parse_string( $this->input );

                $this->expect( $result ) ->to() ->equal( "parsed" );

            });

        });

    });

});
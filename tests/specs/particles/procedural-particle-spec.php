<?php

use Haijin\Parser\Parser;
use Haijin\Parser\Parser_Definition;

$spec->describe( "When matching a procedural particle", function() {

    $this->let( "parser", function() {

        return new Parser( $this->parser_definition );

    });

    $this->describe( "at the begining of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->p() ->str( "123" );

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "p",  function($exp) {

                    $exp->processor( function() {

                        if( $this->peek_char() == "#" ) {

                            $this->set_result( "#" );

                            $this->next_char();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it( "parses the input stream", function() {

            $result = $this->parser->parse_string( "#123" );

            $this->expect( $result ) ->to() ->equal( "#" );

        });

        $this->it( "fails if absent", function() {

            $this->expect( function() {

                $this->parser->parse_string( "z123" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "z123". At line: 1 column: 1.'
                    );
            }); 

        });

        $this->it( "fails if the following particle fails", function() {

            $this->expect( function() {

                $this->parser->parse_string( "#12" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "12". At line: 1 column: 2.'
                    );
            }); 

        });

    });

    $this->describe( "in the middle of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "123" ) ->p() ->str( "321" );

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "p",  function($exp) {

                    $exp->processor( function() {

                        if( $this->peek_char() == "#" ) {

                            $this->set_result( "#" );

                            $this->next_char();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it( "parses the input stream", function() {

            $result = $this->parser->parse_string( "123#321" );

            $this->expect( $result ) ->to() ->equal( "#" );

        });

        $this->it( "fails if absent", function() {

            $this->expect( function() {

                $this->parser->parse_string( "123321" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "321". At line: 1 column: 4.'
                    );
            }); 

        });

        $this->it( "fails if the following particle fails", function() {

            $this->expect( function() {

                $this->parser->parse_string( "123#12" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "12". At line: 1 column: 5.'
                    );
            }); 

        });

    });

    $this->describe( "at the end of an expression", function() {

        $this->let( "parser_definition", function() {

            return ( new Parser_Definition() )->define( function($parser) {

                $parser->expression( "root",  function($exp) {

                    $exp->matcher( function($exp) {

                        $exp ->str( "123" ) ->p();

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

                $parser->expression( "p",  function($exp) {

                    $exp->processor( function() {

                        if( $this->peek_char() == "#" ) {

                            $this->set_result( "#" );

                            $this->next_char();

                            return true;

                        }

                        return false;

                    });

                    $exp->handler( function($string) {
                        return $string;
                    });

                });

            });

        });

        $this->it( "parses the input stream", function() {

            $result = $this->parser->parse_string( "123#" );

            $this->expect( $result ) ->to() ->equal( "#" );

        });

        $this->it( "fails if absent", function() {

            $this->expect( function() {

                $this->parser->parse_string( "123" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected end of stream. At line: 1 column: 4.'
                    );
            }); 

        });

        $this->it( "fails if does not match", function() {

            $this->expect( function() {

                $this->parser->parse_string( "123z" );

            }) ->to() ->raise(
                \Haijin\Parser\Unexpected_Expression_Error::class,
                function($error) {

                    $this->expect( $error->getMessage() ) ->to() ->equal(
                        'Unexpected expression "z". At line: 1 column: 4.'
                    );
            }); 

        });

    });

});
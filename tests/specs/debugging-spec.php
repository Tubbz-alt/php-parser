<?php

use Haijin\Errors\Haijin_Error;
use Haijin\Parser\Particles\Blank_Particle;
use Haijin\Parser\Particles\End_Of_Stream_Particle;
use Haijin\Parser\Particles\End_Of_Expression_Particle;
use Haijin\Parser\Particles\End_Of_Line_Particle;
use Haijin\Parser\Particles\Procedural_Particle;
use Haijin\Parser\Particles\Space_Particle;
use Haijin\Parser\Particles\Sub_Expression_Particle;
use Haijin\Parser\Context_Frame;

$spec->describe( "When debugging expressions", function() {

    $this->it( "returns a End_Of_Line_Particle print string", function() {

        $particle = new End_Of_Line_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "eol()" );

    });

    $this->it( "returns a Blank_Particle print string", function() {

        $particle = new Blank_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "blank()" );

    });

    $this->it( "returns a End_Of_Line_Particle print string", function() {

        $particle = new End_Of_Line_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "eol()" );

    });

    $this->it( "returns a End_Of_Stream_Particle print string", function() {

        $particle = new End_Of_Stream_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "eos()" );

    });

    $this->it( "returns a Procedural_Particle print string", function() {

        $particle = new Procedural_Particle( function(){} );

        $this->expect( (string) $particle )
            ->to() ->equal( "procedural(\$closure)" );

    });

    $this->it( "returns a Space_Particle print string", function() {

        $particle = new Space_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "space()" );

    });

    $this->it( "returns a Sub_Expression_Particle print string", function() {

        $particle = new Sub_Expression_Particle( 'sub_exp' );

        $this->expect( (string) $particle )
            ->to() ->equal( "exp('sub_exp')" );

    });

    $this->it( "returns a End_Of_Expression_Particle print string", function() {

        $particle = new End_Of_Expression_Particle();

        $this->expect( (string) $particle )
            ->to() ->equal( "end-of-expression" );

    });

    $this->describe( 'a Context_Frame', function() {

        $this->it( "raises an error when getting a property", function() {

            $this->expect( function() {

                $context_frame = new Context_Frame();
                $context_frame->prop;

            }) ->to() ->raise(
                Haijin_Error::class,
                function($error) {
                    $this->expect( $error->getMessage() ) ->to()
                    ->equal( 'The Context_Frame does not accept dymamic properties.' );
                }
            );

        });

        $this->it( "raises an error when setting a property", function() {

            $this->expect( function() {

                $context_frame = new Context_Frame();
                $context_frame->prop = 123;

            }) ->to() ->raise(
                Haijin_Error::class,
                function($error) {
                    $this->expect( $error->getMessage() ) ->to()
                    ->equal( 'The Context_Frame does not accept dymamic properties.' );
                }
            );

        });

    });

});
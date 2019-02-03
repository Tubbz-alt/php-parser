<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;
use Haijin\Ordered_Collection;

class Expression
{
    protected $name;
    protected $particles;
    protected $handler_closure;

    /// Initializing

    public function __construct($name)
    {
        $this->name = $name;
        $this->handler_closure = null;
        $this->particle_sequences = Create::an( Ordered_Collection::class )->with();
        $this->particle_sequences->add(
            Create::an( Ordered_Collection::class )->with()
        );
    }

    /// Accessing

    public function get_name()
    {
        return $this->name;
    }

    public function get_particle_sequences()
    {
        return $this->particle_sequences;
    }

    public function get_handler_closure()
    {
        return $this->handler_closure;
    }

    /// DSL

    public function matcher($closure)
    {
        $closure->call( $this );

        $this->append_end_of_sequence_to_each_particle_sequence();
    }

    protected function append_end_of_sequence_to_each_particle_sequence()
    {
        $this->particle_sequences->each_do( function ($particles_sequence) {

            $particles_sequence->add(
                Create::an( End_Of_Expression_Particle::class )->with()
            );

        });
    }

    public function processor($closure)
    {
        $this->matcher( function() use($closure) {

            $this->proc( $closure );

        });
    }

    public function m_regex($regex_string)
    {
        $this->proc( function() use($regex_string) {

            $matches = [];

            \preg_match(
                $regex_string . "A",
                $this->string,
                $matches,
                0,
                $this->context_frame->char_index
            );

            if( empty( $matches ) ) {
                return false;
            }

            $this->skip_chars( strlen( $matches[ 0 ] ) );
            $this->set_result( array_slice( $matches, 1 ) );

            return true;

        });

        return $this;
    }

    public function regex($regex_string)
    {
        $this->proc( function() use($regex_string) {

            $matches = [];

            \preg_match(
                $regex_string . "A",
                $this->string,
                $matches,
                0,
                $this->context_frame->char_index
            );

            if( empty( $matches ) ) {
                return false;
            }

            $this->skip_chars( strlen( $matches[ 0 ] ) );

            $this->set_result(
                isset( $matches[ 1 ] ) ? $matches[ 1 ] : $matches[ 0 ]
            );

            return true;

        });

        return $this;
    }

    public function exp($expression_name)
    {
        $this->add_particle(
            Create::a( Sub_Expression_Particle::class )->with( $expression_name )
        );

        return $this;
    }

    public function str($string)
    {
        $this->proc( function() use($string) {

            $string_length = strlen( $string );

            if( $this->context_frame->char_index + $string_length
                >
                $this->string_length
              )
            {
                return false;
            }

            if( substr_compare(
                    $this->string,
                    $string,
                    $this->context_frame->char_index,
                    $string_length
                )
                !=
                0
              )
            {
                return false;
            }

            $this->skip_chars(  $string_length );

            return true;

        });

        return $this;
    }

    public function sym($string)
    {
        $this->proc( function() use($string) {

            $string_length = strlen( $string );

            if( $this->context_frame->char_index + $string_length
                >
                $this->string_length
              )
            {
                return false;
            }

            if( substr_compare(
                    $this->string,
                    $string,
                    $this->context_frame->char_index,
                    $string_length
                )
                !=
                0
              )
            {
                return false;
            }

            $this->skip_chars( strlen( $string ) );

            $this->set_result( $string );

            return true;

        });

        return $this;
    }

    public function space()
    {
        $this->add_particle(
            Create::an( Space_Particle::class )->with()
        );

        return $this;
    }

    public function blank()
    {
        $this->add_particle(
            Create::an( Blank_Particle::class )->with()
        );

        return $this;
    }

    public function cr()
    {
        $this->proc( function() {

            if( $this->string[ $this->context_frame->char_index ] != "\n" ) {

                return false;

            }

            $this->skip_chars( 1 );

            $this->new_line();

            return true;

        });

        return $this;
    }

    public function eos()
    {
        $this->add_particle(
            Create::an( End_Of_Stream_Particle::class )->with()
        );

        return $this;
    }

    public function eol()
    {
        $this->add_particle(
            Create::an( End_Of_Line_Particle::class )->with()
        );

        return $this;
    }

    public function proc($closure)
    {
        $this->add_particle(
            Create::an( Procedural_Particle::class )->with( $closure )
        );

        return $this;
    }

    public function opt($particle)
    {
        $this->particle_sequences->last()->last()->be_optional();

        return $this;
    }

    public function or()
    {
        $this->particle_sequences->add(
            Create::an( Ordered_Collection::class )->with()
        );

        return $this;
    }

    public function handler($closure)
    {
        $this->handler_closure = $closure;
    }

    protected function add_particle($particle)
    {
        $this->particle_sequences->last()->add( $particle );
    }

    public function __call($method_name, $params)
    {
        return $this->exp( $method_name );
    }

}
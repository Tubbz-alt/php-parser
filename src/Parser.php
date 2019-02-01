<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;
use Haijin\Ordered_Collection;

class Parser
{
    public $string;
    public $string_length;

    public $char_index;
    public $line_index;
    public $column_index;

    /// Initializing

    public function __construct($parser_definition)
    {
        $this->parser_definition = $parser_definition;

        $this->string = null;
        $this->string_length = 0;

        $this->context_frame = $this->new_context_frame();
        $this->frames_stack = Create::an( Ordered_Collection::class )->with();

        $this->parsing_error = null;

        $this->undefined = new \stdclass();
    }

    protected function new_context_frame()
    {
        return Create::a( Context_Frame::class )->with();
    }

    /// Parsing inputs

    public function parse($file)
    {
        return $this->parse_string( \file_get_contents( $file ) );
    }

    public function parse_string($string)
    {
        $this->string = $string;
        $this->string_length = strlen( $this->string );

        $this->context_frame->char_index = 0;
        $this->context_frame->line_index = 1;
        $this->context_frame->column_index = 1;

        if( $this->has_before_parsing_closure() ) {
            $this->evaluate_before_parsing_closure();
        }

        $this->begin_expression( "root" );

        $this->do_parsing_loop();

        return $this->context_frame->get_handler_params()[ 0 ];
    }

    /// Parsing particles loops

    protected function do_parsing_loop()
    {
        while( $this->context_frame->has_expected_particles() )
        {
            if( ! $this->context_frame->has_current_expression() ) {
                break;
            }

            $particle = $this->context_frame->next_expected_particle();

            if( $this->at_end_of_stream()
                &&
                ! is_a( $particle, End_Of_Expression_Particle::class )
              )
            {
                $this->on_unexpected_particle();    
                continue;
            }

            $this->parse_particle( $particle );
        }

        if( $this->not_end_of_stream() ) {
            $this->raise_unexpected_expression_error();
        }
    }

    public function parse_particle($particle)
    {
        $this->context_frame->set_particle_result( $this->undefined );

        $parsed = $particle->parse_with( $this );

        $result = $this->context_frame->get_particle_result();

        $this->context_frame->set_particle_result( $this->undefined );

        if( $parsed ) {

            if( $result !== $this->undefined ) {
                $this->context_frame->add_handler_param( $result );
            }

        } else {

            $this->on_unexpected_particle();

        }
    }

    /// Expressions stack

    protected function save_current_context()
    {
        $this->frames_stack->add( $this->context_frame );

        $this->context_frame = clone $this->context_frame;
    }

    protected function restore_previous_context()
    {
        $this->context_frame = $this->frames_stack->remove_last();
    }

    protected function get_previous_context()
    {
        return $this->frames_stack->last();
    }

    /// Before parsing closure

    protected function has_before_parsing_closure()
    {
        return $this->parser_definition->get_before_parsing_closure() !== null;
    }

    protected function evaluate_before_parsing_closure()
    {
        return $this->parser_definition->get_before_parsing_closure()->call( $this );        
    }

    /// Expressions

    protected function get_expression_named($expression_name)
    {
        return $this->parser_definition->get_expression_named(
            $expression_name,
            function() use($expression_name) {

                $this->raise_expression_not_found_error( $expression_name );

        }, $this );
    }

    protected function begin_expression($expression_name)
    {
        $this->save_current_context();

        $this->context_frame->set_current_expression(
            $this->get_expression_named( $expression_name )
        );

        $this->context_frame->set_expected_particles_sequences(
            $this->context_frame->get_current_expression()->get_particle_sequences()
        );

        $this->context_frame->set_expected_particles(
            $this->context_frame->next_expected_particles_sequence()
        );

        $this->context_frame->set_handler_params( [] );
        $this->context_frame->set_expression_result( null );
    }

    protected function end_matched_expression()
    {
        $current_context = $this->context_frame;

        $this->restore_previous_context();

        $this->context_frame->char_index = $current_context->char_index;
        $this->context_frame->line_index = $current_context->line_index;
        $this->context_frame->column_index = $current_context->column_index;

        $this->context_frame->add_handler_param( $current_context->get_expression_result() );

        $this->parsing_error = null;
    }

    protected function end_unmatched_expression()
    {
        if( $this->frames_stack->is_empty() ) {
            throw $this->parsing_error;
        }

        $this->restore_previous_context();

        $this->on_unexpected_particle();
    }

    /// Particles

    protected function on_unexpected_particle()
    {
        if( ! $this->context_frame->has_expected_particles_sequences() ) {

            if( $this->parsing_error === null ) {
                $this->parsing_error = $this->new_unexpected_expression_error();
            }

            $this->end_unmatched_expression();
            
            return; 
        }

        $this->begin_next_particles_sequence();
    }

    protected function begin_next_particles_sequence()
    {
        $this->context_frame->set_expected_particles(
            $this->context_frame->next_expected_particles_sequence()
        );

        $previous_context = $this->get_previous_context();

        $this->context_frame->char_index = $previous_context->char_index;
        $this->context_frame->line_index = $previous_context->line_index;
        $this->context_frame->column_index = $previous_context->column_index;

        $this->context_frame->set_handler_params( [] );
    }

    protected function end_particles_sequence()
    {
        $handler_result = $this->context_frame->get_current_expression()->get_handler_closure()
            ->call( $this, ...$this->context_frame->get_handler_params() );

        $this->context_frame->set_expression_result( $handler_result );

        $this->end_matched_expression();

        return true;
    }

    /// Particle methods

    public function parse_sub_expression_particle($sub_expression_particle)
    {
        $this->begin_expression( $sub_expression_particle->get_sub_expression_name() );

        return true;
    }

    public function parse_procedural_particle($procedural_particle)
    {
        return $procedural_particle->get_closure()->call( $this );

        return true;
    }


    public function parse_end_of_expression_particle($end_of_expression_particle)
    {
        $this->end_particles_sequence();

        return true;
    }

    /// Stream methods

    protected function at_end_of_stream()
    {
        return $this->context_frame->char_index >= $this->string_length;
    }

    protected function not_end_of_stream()
    {
        return $this->context_frame->char_index < $this->string_length;
    }

    public function new_line()
    {
        $this->context_frame->line_index += 1;
        $this->context_frame->column_index = 1;
    }

    public function increment_stream_by($n)
    {
        $this->increment_char_index_by( $n );
        $this->increment_column_index_by( $n );
    }

    public function increment_char_index_by($n)
    {
        $this->context_frame->char_index += $n;
    }

    public function increment_column_index_by($n)
    {
        $this->context_frame->column_index += $n;
    }

    public function current_string()
    {
        return \substr( $this->string, $this->context_frame->char_index );
    }

    public function next_char()
    {
        $this->increment_stream_by( 1 );

        return $this->current_char_at( -1 );
    }

    public function current_char_at($offset)
    {
        return $this->string[ $this->context_frame->char_index + $offset ];
    }

    public function set_result($object)
    {
        $this->context_frame->set_particle_result( $object );
    }

    public function current_line()
    {
        return $this->context_frame->line_index;
    }

    public function current_column()
    {
        return $this->context_frame->column_index;
    }

    public function current_char_pos()
    {
        return $this->context_frame->char_index;
    }

    /// Custom methods

    public function __call($method_name, $params )
    {
        $closure = $this->parser_definition->custom_method_at( $method_name, function() use($method_name) {

            $this->raise_method_not_found_error( $method_name );

        }, $this );

        return $closure->call( $this, ...$params );
    }

    /// Raising errors

    protected function raise_unexpected_expression_error()
    {
        throw $this->new_unexpected_expression_error();
    }

    protected function new_unexpected_expression_error()
    {
        $matches = [];

        preg_match(
            "/.*(?=\n?)/A",
            $this->string,
            $matches,
            0,
            $this->context_frame->char_index
        );

        return Create::an( Unexpected_Expression_Error::class ) ->with(
            "Unexpected expression \"{$matches[0]}\". At line: {$this->current_line()} column: {$this->current_column()}."
        );
    }

    protected function raise_method_not_found_error($method_name)
    {
        throw Create::an( \Haijin\Parser\Method_Not_Found_Error::class ) ->with(
            "The method \"{$method_name}\" was not found in this parser.",
            $method_name,
            $this
        );
    }


    protected function raise_expression_not_found_error($expression_name)
    {
        throw Create::an( \Haijin\Parser\Expression_Not_Found_Error::class ) ->with(
            "The expression \"{$expression_name}\" was not found in this parser.",
            $expression_name,
            $this
        );
    }

}
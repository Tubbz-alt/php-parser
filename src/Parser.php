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
                ! $particle->matches_eos()
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

        if( $particle->is_optional() ) {
            $saved_context = clone $this->context_frame;
        }

        $parsed = $particle->parse_with( $this );

        if( $parsed ) {

            $result = $this->context_frame->get_particle_result();

            if( $result !== $this->undefined ) {
                $this->context_frame->add_handler_param( $result );
            }

        } elseif( $particle->is_optional() ) {

                $this->context_frame = $saved_context;

                $this->context_frame->add_handler_param( null );

        } else {

            $this->context_frame->set_particle_result( $this->undefined );

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
        $handler_closure = $this->context_frame->get_current_expression()->get_handler_closure();

        if( $handler_closure !== null ) {

            $handler_result = $handler_closure ->call(
                $this,
                ...$this->context_frame->get_handler_params()
            );

            $this->context_frame->set_expression_result( $handler_result );
        }

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
    }

    public function parse_blank_particle($blank_particle)
    {
        if( $this->at_end_of_stream() ) {
            return true;
        }

        $char = $this->peek_char( 0 );

        while( $char == " " || $char == "\t" || $char == "\n" ) {

            if( $char == "\n" ) {
                $this->new_line();
            }

            $this->skip_chars( 1 );

            if( $this->at_end_of_stream() ) {
                break;
            }

            $char = $this->peek_char( 0 );
        }

        return true;
    }

    public function parse_space_particle($space_particle)
    {
        if( $this->at_end_of_stream() ) {
            return true;
        }

        $char = $this->peek_char( 0 );

        while( $char == " " || $char == "\t" ) {

            $this->skip_chars( 1 );

            if( $this->at_end_of_stream() ) {
                break;
            }

            $char = $this->peek_char( 0 );
        }

        return true;
    }

    public function parse_eos_particle($eos_particle)
    {
        return $this->at_end_of_stream();
    }

    public function parse_eol_particle($eol_particle)
    {
        if( $this->at_end_of_stream() ) {
            return true;
        }

        if( $this->peek_char() == "\n" ) {
            $this->skip_chars( 1 );
            $this->new_line();
            return true;
        }

        return false;
    }

    public function parse_end_of_expression_particle($end_of_expression_particle)
    {
        $this->end_particles_sequence();

        return true;
    }

    /// Stream methods

    /**
     * Returns true if the stream is beyond its last char, false otherwise.
     */
    protected function at_end_of_stream()
    {
        return $this->context_frame->char_index >= $this->string_length;
    }

    /**
     * Returns true if the stream has further chars, false otherwise.
     */
    protected function not_end_of_stream()
    {
        return $this->context_frame->char_index < $this->string_length;
    }

    /**
     * Increments by one the input line counter and resets the column counter to 1.
     *
     * Call this method when the parser encounters a "\n" character in order
     * to keep track of the correct line and column indices used in error messages.
     *
     * If this method is not properly called, the parser will still correctly parse
     * valid inputs but the error messages for invalid inputs will be invalid.
     */
    public function new_line()
    {
        $this->context_frame->line_index += 1;
        $this->context_frame->column_index = 1;
    }

    /**
     * Increments the stream pointer and the column counter by $n.
     *
     * Use these method to move backwards or foreward in the stream skipping chars.
     */
    public function skip_chars($n)
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

    /**
     * Returns the tail of the stream which has not been parsed yet.
     *
     * Use this method only to debug the parsing process. Using it for the actual
     * parsing of the input will probably be very inneficient.
     */
    public function current_string()
    {
        return \substr( $this->string, $this->context_frame->char_index );
    }

    /**
     * Returns the current char in the stream and moves forward the stream pointer by one.
     */
    public function next_char()
    {
        $this->skip_chars( 1 );

        return $this->peek_char_at( -1 );
    }

    /**
     * Returns the current char in the stream. Does not modify the stream.
     */
    public function peek_char()
    {
        return $this->peek_char_at( 0 );
    }

    /**
     * Returns the char at an $offset from its current position. Does not modify the stream.
     */
    public function peek_char_at($offset)
    {
        return $this->string[ $this->context_frame->char_index + $offset ];
    }

    /**
     * Sets the result of the particle to be an $object.
     *
     * The result of a particle can be any object, it does not need to be the actual parsed
     * input.
     */
    public function set_result($object)
    {
        $this->context_frame->set_particle_result( $object );
    }

    /**
     * Returns the current line index.
     *
     * Use this method for debugging and for error messages.
     */
    public function current_line()
    {
        return $this->context_frame->line_index;
    }

    /**
     * Returns the current column index in the current line.
     *
     * Use this method for debugging and for error messages.
     */
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
        if( $this->at_end_of_stream() ) {

            $expression = "end of stream";

        } elseif( $this->peek_char() == "\n" ) {

            $expression = '"\\n"';

        } else {

            $matches = [];

            preg_match(
                "/.*(?=\n?)/A",
                $this->string,
                $matches,
                0,
                $this->context_frame->char_index
            );

            $expression = "expression \"{$matches[0]}\"";
        }

        return Create::an( Unexpected_Expression_Error::class ) ->with(
            "Unexpected {$expression}. At line: {$this->current_line()} column: {$this->current_column()}."
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
<?php

namespace Haijin\Parser;

use Haijin\Instantiator\Create;
use Haijin\Ordered_Collection;

class Context_Frame
{
    public $char_index;
    public $line_index;
    public $column_index;

    protected $current_expression;
    protected $current_particles_sequences;
    protected $expected_particles;

    protected $handler_params;
    protected $expression_result;
    protected $expression_is_optional;
    protected $particle_result;

    /// Initializing

    public function __construct()
    {
        $this->char_index = null;
        $this->line_index = 1;
        $this->column_index = 1;

        $this->current_expression = null;
        $this->current_particles_sequences = Create::an( Ordered_Collection::class )->with();
        $this->expected_particles = Create::an( Ordered_Collection::class )->with();

        $this->handler_params = [];
        $this->expression_result = null;
        $this->expression_is_optional = false;
    }

    /// Expected expression

    public function set_current_expression($expression)
    {
        $this->current_expression = $expression;
    }

    public function get_current_expression()
    {
        return $this->current_expression;
    }

    public function has_current_expression()
    {
        return $this->current_expression !== null;
    }

    /// Expected particles sequence

    public function set_expected_particles($particles_sequence)
    {
        $this->expected_particles = clone $particles_sequence;
    }

    public function has_expected_particles()
    {
        return $this->expected_particles->not_empty();
    }

    public function next_expected_particle()
    {
        return $this->expected_particles->remove_first();
    }

    /// Expected particles sequences

    public function set_expected_particles_sequences($particles_sequences)
    {
        $this->current_particles_sequences = clone $particles_sequences;
    }

    public function has_expected_particles_sequences()
    {
        return $this->current_particles_sequences->not_empty();
    }

    public function next_expected_particles_sequence()
    {
        return $this->current_particles_sequences->remove_first();
    }

    /// Handler parameters

    public function set_handler_params($params)
    {
        $this->handler_params = $params;
    }

    public function get_handler_params()
    {
        return $this->handler_params;
    }

    public function add_handler_param($object)
    {
        return $this->handler_params[] = $object;
    }

    /// Expression result

    public function set_expression_result($object)
    {
        $this->expression_result = $object;
    }

    public function get_expression_result()
    {
        return $this->expression_result;
    }

    public function set_expression_is_optional($is_optional)
    {
        $this->expression_is_optional = $is_optional;
    }

    public function expression_is_optional()
    {
        return $this->expression_is_optional;
    }

    /// Particle result

    public function set_particle_result($object)
    {
        $this->particle_result = $object;
    }

    public function get_particle_result()
    {
        return $this->particle_result;
    }

    /// Error handling

    public function __set($property, $value)
    {
        throw new Exception( "Error" );
    }

    public function __get($property)
    {
        throw new Exception( "Error" );
    }
}
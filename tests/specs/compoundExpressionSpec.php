<?php

use Haijin\Parser\Errors\UnexpectedExpressionError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching a compound particles expression", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("parserDefinition", function () {

        return (new ParserDefinition())->define(function ($parser) {

            $parser->expression("root", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->integer()->str("+")->integer();

                });

                $exp->handler(function ($leftInteger, $rightInteger) {

                    return $leftInteger + $rightInteger;

                });

            });

            $parser->expression("integer", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->regex("/([0-9]+)/");

                });

                $exp->handler(function ($integerString) {

                    return (int)$integerString;

                });

            });

        });

    });

    $this->describe("for each matched expression found", function () {

        $this->let("input", function () {
            return "3+4";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->be("===")->than(7);

        });

    });


    $this->describe("for an unexpected expression at the beginning", function () {

        $this->let("input", function () {
            return "a+4";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "a+4". At line: 1 column: 1.'
                    );
                });

        });

    });

    $this->describe("for an unexpected expression after an expected expression", function () {

        $this->let("input", function () {
            return "3+a";
        });

        $this->it("raises an error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                UnexpectedExpressionError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal(
                        'Unexpected expression "a". At line: 1 column: 3.'
                    );
                });

        });

    });

});
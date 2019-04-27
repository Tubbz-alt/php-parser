<?php

use Haijin\Parser\Errors\MethodNotFoundError;
use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;


$spec->describe("When calling custom methods in the parser", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("input", function () {
        return "1";
    });

    $this->describe("if the method is defined", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->def("custom", function ($n, $m) {

                    return $n + $m;

                });

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("1");

                    });

                    $exp->handler(function () {

                        return $this->custom(3, 4);

                    });

                });

            });

        });

        $this->it("evaluates the method and returns the result", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(7);

        });

    });

    $this->describe("if the method is not defined", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("1");

                    });

                    $exp->handler(function () {

                        return $this->custom(3, 4);

                    });

                });

            });

        });

        $this->it("raises a undefined custom method error", function () {

            $this->expect(function () {

                $this->parser->parseString($this->input);

            })->to()->raise(
                MethodNotFoundError::class,
                function ($error) {

                    $this->expect($error->getMessage())->to()->equal('The method "custom" was not found in this parser.');

                    $this->expect($error->getMethodName())->to()
                        ->equal("custom");

                    $this->expect($error->getParser())->to()
                        ->be("===")->than($this->parser);
                }
            );

        });

    });

});
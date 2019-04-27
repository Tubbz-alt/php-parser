<?php

use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("Before parsing an input", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("input", function () {
        return "1";
    });

    $this->describe("if a beforeParsing closure is defined", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->beforeParsing(function () {

                    $this->beforeParsingClosureEvaluated = true;

                });

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("1");

                    });

                    $exp->handler(function () {

                        return "parsed";

                    });

                });

            });

        });

        $this->it("evaluates the closure before start to parse the input", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal("parsed");

            $this->expect($this->parser->beforeParsingClosureEvaluated)->to()
                ->be()->true();

        });

    });

    $this->describe("if no beforeParsing closure is defined", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->str("1");

                    });

                    $exp->handler(function () {

                        return "parsed";

                    });

                });

            });

        });

        $this->it("does not fail", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal("parsed");

        });

    });

});
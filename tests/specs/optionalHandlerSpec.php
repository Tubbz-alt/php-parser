<?php

use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When an expression does not define a handler", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("parserDefinition", function () {

        return (new ParserDefinition())->define(function ($parser) {

            $parser->expression("root", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->withHandler()->noHandler();

                });

                $exp->handler(function ($value) {

                    return $value;

                });

            });

            $parser->expression("withHandler", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->str("1");

                });

                $exp->handler(function () {

                    return "parsed";

                });

            });

            $parser->expression("noHandler", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->str("2");

                });

            });

        });

    });

    $this->let("input", function () {
        return "12";
    });

    $this->it("no handler is evaluated for that expression", function () {

        $result = $this->parser->parseString($this->input);

        $this->expect($result)->to()->equal("parsed");

    });

});
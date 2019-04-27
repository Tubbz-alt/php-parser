<?php

use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching an optional particle", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->describe("at the beginning of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp
                            ->opt($exp->integer())->str("a")->str("b");

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($string) {
                        return (int)$string;
                    });

                });

            });

        });

        $this->describe("when the optional particle is present", function () {

            $this->let("input", function () {
                return "1ab";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal(1);

            });

        });

        $this->describe("when the optional particle is absent", function () {

            $this->let("input", function () {
                return "ab";
            });

            $this->it("evaluates the handler with null", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be()->null();

            });

        });

    });

    $this->describe("in the middle of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp
                            ->str("a")->opt($exp->integer())->str("b");

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($string) {
                        return (int)$string;
                    });

                });

            });

        });

        $this->describe("when the optional particle is present", function () {

            $this->let("input", function () {
                return "a2b";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal(2);

            });

        });

        $this->describe("when the optional particle is absent", function () {

            $this->let("input", function () {
                return "ab";
            });

            $this->it("evaluates the handler with null", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be()->null();

            });

        });

    });

    $this->describe("at the end of an expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp
                            ->str("a")->str("b")->opt($exp->integer());

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($string) {
                        return (int)$string;
                    });

                });

            });

        });

        $this->describe("when the optional particle is present", function () {

            $this->let("input", function () {
                return "ab3";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal("3");

            });

        });

        $this->describe("when the optional particle is absent", function () {

            $this->let("input", function () {
                return "ab";
            });

            $this->it("evaluates the handler with null", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be()->null();

            });

        });

    });

    $this->describe("as a single expression", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp
                            ->opt($exp->integer());

                    });

                    $exp->handler(function ($string) {
                        return $string;
                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($string) {
                        return (int)$string;
                    });

                });

            });

        });

        $this->describe("when the optional particle is present", function () {

            $this->let("input", function () {
                return "1";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal("1");

            });

        });

        $this->describe("when the optional particle is absent", function () {

            $this->let("input", function () {
                return "";
            });

            $this->it("evaluates the handler with null", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->be()->null();

            });

        });

    });

    $this->describe("as consecutives expressions", function () {

        $this->let("parserDefinition", function () {

            return (new ParserDefinition())->define(function ($parser) {

                $parser->expression("root", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp
                            ->opt($exp->integer())
                            ->opt($exp->str(" "))
                            ->opt($exp->integer());

                    });

                    $exp->handler(function ($int_1, $int_2) {
                        return [$int_1, $int_2];
                    });

                });

                $parser->expression("integer", function ($exp) {

                    $exp->matcher(function ($exp) {

                        $exp->regex("/([0-9]+)/");

                    });

                    $exp->handler(function ($string) {
                        return (int)$string;
                    });

                });

            });

        });

        $this->describe("when both optional particles are present", function () {

            $this->let("input", function () {
                return "1 2";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal([1, 2]);

            });

        });

        $this->describe("when the first optional particle is present", function () {

            $this->let("input", function () {
                return "1";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal([1, null]);

            });

        });

        $this->describe("when the second optional particle is present", function () {

            $this->let("input", function () {
                return " 2";
            });

            $this->it("evaluates the handler with its value", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal([null, 2]);

            });

        });

        $this->describe("when both particles are absent", function () {

            $this->let("input", function () {
                return "";
            });

            $this->it("evaluates the handler with null", function () {

                $result = $this->parser->parseString($this->input);

                $this->expect($result)->to()->equal([null, null]);

            });

        });

    });

});
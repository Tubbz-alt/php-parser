<?php

use Haijin\Parser\Parser;
use Haijin\Parser\ParserDefinition;

$spec->describe("When matching recursive expressions", function () {

    $this->let("parser", function () {

        return new Parser($this->parserDefinition);

    });

    $this->let("parserDefinition", function () {

        return (new ParserDefinition())->define(function ($parser) {

            $parser->def('customMethod', new Custom_Method());

            $parser->expression("root", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->str("[")->space()->integerList()->space()->str("]");

                });

                $exp->handler(function ($integers) {

                    return array_sum($integers) + $this->customMethod(3, 4);

                });

            });

            $parser->expression("integerList", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->integer()->space()->str(",")->space()->integerList()
                        ->or()
                        ->integer();

                });

                $exp->handler(new Integer_List_Handler());

            });

            $parser->expression("integer", function ($exp) {

                $exp->matcher(function ($exp) {

                    $exp->opt($exp->sym("-"))->regex("/([0-9]+)/");

                });

                $exp->handler(new Integer_Handler());

            });

        });

    });

    $this->describe("when the input matches a recursive expression", function () {

        $this->let("input", function () {
            return "[1, 2, 3, 4]";
        });

        $this->it("evaluates the handler closure", function () {

            $result = $this->parser->parseString($this->input);

            $this->expect($result)->to()->equal(17);

        });

    });

});

class Integer_List_Handler
{
    public function __invoke($integer, $list = null)
    {
        if ($list === null) {

            return [$integer];

        }

        return array_merge([$integer], $list);
    }
}

class Integer_Handler
{
    public function __invoke($negative, $integerString)
    {
        if ($negative === null) {
            return (int)$integerString;
        } else {
            return -(int)$integerString;
        }
    }
}

class Custom_Method
{
    public function __invoke($n, $m)
    {
        return $n + $m;
    }
}
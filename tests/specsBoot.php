<?php

use Haijin\Debugger;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Html\Facade;

$specs->beforeAll(function () {
    $this->coverage = initializeCoverageReport();
});

$specs->afterAll(function () {
    generateCoverageReport($this->coverage);
});

function initializeCoverageReport()
{
    $coverage = new CodeCoverage;
    $coverage->filter()->addDirectoryToWhitelist('src/');
    $coverage->start('specsCoverage');

    return $coverage;
}

;

function generateCoverageReport($coverage)
{
    $coverage->stop();
    $writer = new Facade;
    $writer->process($coverage, 'coverage-report/');
}

;

function inspect($object)
{
    Debugger::inspect($object);
}
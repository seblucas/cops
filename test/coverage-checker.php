<?php

/**
* Code coverage checker. Analyzes a given `clover.xml` report produced
* by PHPUnit and checks if coverage fits expected ratio
*
* Usage:
* php coverage-checker <path-to-clover> <pass-percentage>
*
* @author Marco Pivetta <ocramius@gmail.com>
* @see http://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/
*/

$inputFile  = $argv[1];
$percentage = min(100, max(0, (int) $argv[2]));

if (!file_exists($inputFile)) {
    echo 'Invalid input file provided';
    exit (0);
}

if (!$percentage) {
    throw new InvalidArgumentException('An integer checked percentage must be given as second parameter');
}

$xml             = new SimpleXMLElement(file_get_contents($inputFile));
$metrics         = $xml->xpath('//metrics');
$totalElements   = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
    $totalElements   += (int) $metric['elements'];
    $checkedElements += (int) $metric['coveredelements'];
}

$coverage = ($checkedElements / $totalElements) * 100;

if ($coverage < $percentage) {
    echo 'Code coverage is ' . $coverage . '%, which is below the accepted ' . $percentage . '%' . PHP_EOL;
    exit(1);
}

echo 'Code coverage is ' . $coverage . '% - OK!' . PHP_EOL;

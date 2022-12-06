<?php

// include('last.class.php');
include('lastslidingwindow.class.php');

$filename = 'input.txt';

$last = new Last(14);

$fp = fopen($filename, 'r');

$noiseLength = 0;

do {
    $noiseLength++;
    $last->add(fgetc($fp));
    if ($last->isDifferent()) {
        break;
    }
} while (!feof($fp));

echo 'Noise and marker length: ' . $noiseLength . "\r\n";
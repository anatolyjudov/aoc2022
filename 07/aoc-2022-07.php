<?php

include('reader.class.php');
include('counter.class.php');
include('shell.class.php');

$reader  = new Reader('input.txt');
$counter = new Counter();
$shell   = new Shell($counter);

do {
    $line = $reader->next();
    if (empty(trim($line))) continue;

    if ($line[0] === '$') {
        $shell->execute($line);
    } else {
        $shell->read($line);
    }

} while (!$reader->end());

echo "==\r\n";

echo 'First star: ' . $counter->sumAllAtMost(100000) . "\r\n";
$spaceAvailable = 70000000 - $counter->getSize('');
echo 'Space available: ' . $spaceAvailable . "\r\n";
$spaceToFree = 30000000 - $spaceAvailable;
echo 'Space to free: ' . $spaceToFree . "\r\n";
echo 'Second star: ' . $counter->getMinAtLeast($spaceToFree) . "\r\n";

<?php

declare(strict_types = 1);

class SnafuNumber
{
    const BASE = 5;
    const DIGITS = [
        '=' => -2,
        '-' => -1,
        '0' => 0,
        '1' => 1,
        '2' => 2
    ];

    protected static array $revertedDigits = [];
    protected array $digits = [];

    protected function getDigitForDecimal(int $a): string
    {
        if (!array_key_exists($a, self::$revertedDigits)) die('Cannot convert decimal to digit: ' . $a . PHP_EOL);
        return (string) self::$revertedDigits[$a];
    }

    public function getDecimal(): int
    {
        $res = 0;
        for ($d = 0; $d < sizeof($this->digits); $d++) {
            $add = ((self::DIGITS[$this->digits[$d]]) < 0 ? -1 : 1) * abs(self::DIGITS[$this->digits[$d]]) * (self::BASE ** $d);
            $res += $add;
        }
        return $res;
    }

    public function getString(): string
    {
        $ret = strrev(implode('', $this->digits));
        return $ret === '' ? '0' : $ret;
    }

    public function __construct(array $digits = [])
    {
        $this->digits = $digits;

        if (empty(self::$revertedDigits)) self::$revertedDigits = array_flip(self::DIGITS);
    }

    public static function createFromString(string $number): SnafuNumber
    {
        $digits = [];
        $number = strrev($number);
        for ($d = 0; $d < strlen($number); $d++) {
            if (!in_array($number[$d], self::DIGITS)) die('Incorrect symbol given: ' . $number);
            $digits[$d] = $number[$d];
        }
        return new SnafuNumber($digits);
    }

    public function add(SnafuNumber $inc): self
    {
        p('adding', $this->getString(), $this->getDecimal(), 'to', $inc->getString(), $inc->getDecimal());

        $sumDigits = [];
        $oDecimal = 0;
        for($d = 0; $d < max(sizeof($this->digits), sizeof($inc->digits)); $d++) {
            $a = $this->digits[$d] ?? 0;
            $b = $inc->digits[$d] ?? 0;
            $cDecimal = self::DIGITS[$a] + self::DIGITS[$b] + $oDecimal;
            if ($cDecimal > 2) {
                $oDecimal = 1;
                $cDecimal = $cDecimal - self::BASE;
            } elseif ($cDecimal < -2) {
                $oDecimal = -1;
                $cDecimal = $cDecimal + self::BASE;
            } else {
                $oDecimal = 0;
            }
            $digit = $this->getDigitForDecimal($cDecimal);
            $sumDigits[$d] = $digit;
        }
        if ($oDecimal !== 0) {
            $sumDigits[] = $this->getDigitForDecimal($oDecimal);
        }
        $this->digits = $sumDigits;
        return $this;
    }
}

$fuelRequirements = [];
foreach(explode(PHP_EOL, file_get_contents('input.txt')) as $numString) {
    if (empty(trim($numString))) continue;
    $fuelRequirements[] = SnafuNumber::createFromString($numString);
}

$sum = new SnafuNumber();
$sumDecimal = 0;
foreach ($fuelRequirements as $fr) {
    $sum->add($fr);
    $sumDecimal += $fr->getDecimal();
    p('Decimal sum:', $sumDecimal, 'Snauf sum', $sum->getString(), 'Snauf decimal value', $sum->getDecimal());
}

function p(...$args)
{
    foreach ($args as $arg) {
        if (is_array($arg) || is_object($arg)) {
            print_r($arg);
            continue;
        }
        echo $arg . ' ';
    }
    echo PHP_EOL;
}
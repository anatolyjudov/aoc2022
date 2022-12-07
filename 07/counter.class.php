<?php

class Counter
{
    protected $counts = [];

    public function parseLsLine($dirContext, $line): void
    {
        if (substr($line, 0, 3) === 'dir') {
            return;
        }
        $size = substr($line, 0, strpos($line, ' '));
        $this->addSize($dirContext, $size);
    }

    public function addSize($dir, $size)
    {
        $offset = 0;
        echo $size . "\r\n";
        while (($pos = strpos($dir, '/', $offset)) !== false) {
            $parent = substr($dir, 0, $pos);
            $this->counts[$parent] = ($this->counts[$parent] ?? 0) + $size;

            $offset = $pos + 1;
        }
    }

    public function print()
    {
        var_dump($this->counts);
    }

    public function sumAllAtMost($max)
    {
        $sum = 0;
        foreach ($this->counts as $count) {
            if ($count <= $max) {
                $sum += $count;
            }
        }
        return $sum;
    }

    public function getSize($dir)
    {
        if (empty($this->counts[$dir])) {
            die('no such dir: ' . $dir);
        }
        return $this->counts[$dir];
    }

    public function getMinAtLeast($limit)
    {
        $minDir = '';
        $min = $this->counts[$minDir];
        foreach ($this->counts as $dir => $count) {
            if (($count >= $limit) && ($count < $min)) {
                $min = $count;
                $minDir = $dir;
            }
        }
        echo $minDir . "\r\n";
        return $min;
    }
}
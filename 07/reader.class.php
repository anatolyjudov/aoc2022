<?php

class Reader
{
    protected $fp;

    public function __construct($filename)
    {
        $this->fp = fopen($filename, 'r');
    }

    public function next(): string
    {
        return rtrim(fgets($this->fp));
    }

    public function end(): bool
    {
        return feof($this->fp);
    }
}
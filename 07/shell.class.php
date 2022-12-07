<?php

class Shell
{
    protected $current = '';
    protected $context = '';
    protected $counter;

    public function __construct(Counter $counter)
    {
        $this->counter = $counter;
    }

    public function read($line): void
    {
        if ($this->context === 'ls') {
            $this->counter->parseLsLine($this->current, $line);
        }
    }

    public function execute($line)
    {
        $command = substr($line, 2, 2);
        switch($command) {
            case 'cd':
                $params = substr($line, 5);
                $this->cd($params);
                break;
            case 'ls':
                $this->ls();
                break;
            default:
                die('Unknown command in the line: ' . $line);
        }
    }

    public function cd($dir): void
    {
        echo "CD $dir\r\n";

        if ($dir === '/') {
            $this->current = '/';
        } elseif ($dir === '..') {
            $this->current = substr(
                $this->current, 0, strrpos($this->current, '/', -2) + 1
            );
        } else {
            $this->current .= $dir . '/';
        }

        echo $this->current . "\r\n";
    }

    public function ls(): void
    {
        $this->context = 'ls';
    }
}
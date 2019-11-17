<?php

class JsonDataHandler implements IDataHandler
{
    const DIR = 'data';
    private $filename;

    public function __construct()
    {
        $this->setFilename('');
    }

    public function setFilename(string $filename): IDataHandler
    {
        $this->filename = $filename;
        return $this;
    }

    public function load(): array
    {
        if ($this->exists()) {
            return json_decode(file_get_contents($this->getPath()), true);
        }
        return [];
    }

    public function save(array $data, bool $rewrite = true): bool
    {
        if (!$rewrite && $this->exists()) {
            return false;
        }
        $fd = fopen($this->getPath(), 'w');
        if ($fd) {
            if (fwrite($fd, json_encode($data, JSON_UNESCAPED_UNICODE)) !== false) {
                fclose($fd);
                return true;
            }
        }
        return false;
    }

    public function exists(): bool
    {
        return file_exists($this->getPath());
    }

    public function getPath()
    {
        return self::DIR . "/$this->filename.json";
    }
}

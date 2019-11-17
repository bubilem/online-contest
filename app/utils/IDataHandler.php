<?php

/**
 * Simple data handler interface
 */
interface IDataHandler
{
    public function setFilename(string $filename): IDataHandler;
    public function load(): array;
    public function save(array $data): bool;
    public function exists(): bool;
}

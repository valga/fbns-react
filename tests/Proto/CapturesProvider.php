<?php

declare(strict_types=1);

namespace Fbns\Tests\Proto;

use Iterator;

class CapturesProvider implements Iterator
{
    /** @var Iterator */
    private $iterator;

    public function __construct(string $path)
    {
        $this->iterator = new \RegexIterator(
            new \DirectoryIterator($path),
            '#\.thrift$#'
        );
    }

    public function current()
    {
        /** @var \DirectoryIterator $file */
        $file = $this->iterator->current();

        return [basename($file->getBasename()), file_get_contents($file->getRealPath())];
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}

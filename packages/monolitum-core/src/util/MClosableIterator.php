<?php

namespace monolitum\core\util;

interface MClosableIterator
{

    public function hasNext(): bool;

    public function nextConsume(): mixed;

    public function firstAndClose(): mixed;

    public function close(): void;

}

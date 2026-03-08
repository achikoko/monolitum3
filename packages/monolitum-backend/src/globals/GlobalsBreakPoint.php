<?php

namespace monolitum\backend\globals;

readonly class GlobalsBreakPoint{
    public function __construct(
        public int   $uniqueId,
        public array $uniqueIdByContext = []
    )
    {
    }
}

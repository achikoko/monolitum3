<?php

namespace monolitum\backend\params;

interface ParamsProvider_SupportsKeySeeking extends ParamsProvider
{

    public function validateKeyStartingWith_ReturnEnding(string $prefix): ?string;

}

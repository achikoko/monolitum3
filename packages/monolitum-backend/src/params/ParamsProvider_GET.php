<?php

namespace monolitum\backend\params;

class ParamsProvider_GET extends ParamsProvider_FromGlobalArray
{

    public function __construct()
    {
        parent::__construct($_GET);
    }

}

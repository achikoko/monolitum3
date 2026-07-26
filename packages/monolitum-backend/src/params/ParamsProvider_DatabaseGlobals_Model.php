<?php

namespace monolitum\backend\params;

use monolitum\model\Attr;
use monolitum\model\Model;

class ParamsProvider_DatabaseGlobals_Model
{

    function __construct(
        public Model|string     $model,

        public Attr|string      $key,
        public Attr|string      $value,
    )
    {

    }

}

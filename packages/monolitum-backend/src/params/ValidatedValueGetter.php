<?php

namespace monolitum\backend\params;

use monolitum\model\ValidatedValue;

interface ValidatedValueGetter
{
    function getValidatedValue(): ValidatedValue;

}

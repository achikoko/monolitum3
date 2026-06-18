<?php

namespace monolitum\frontend\component;

use monolitum\frontend\AppendTextTrait;
use monolitum\frontend\HtmlElementNode;

abstract class AbstractTextNode extends HtmlElementNode
{
    use AppendTextTrait;

    public function __construct($element, $builder = null)
    {
        parent::__construct($element, $builder);
    }

}

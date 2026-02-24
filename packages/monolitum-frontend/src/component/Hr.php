<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class Hr extends HtmlElementNode
{

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("hr"), $builder);
    }

}

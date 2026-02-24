<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class Ul extends HtmlElementNode
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("ul"), $builder);
    }

}

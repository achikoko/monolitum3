<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable_Node;

class Li extends AbstractTextNode
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("li"), $builder);
    }

}

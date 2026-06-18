<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class Br extends HtmlElementNode
{

    public function __construct()
    {
        parent::__construct(new HtmlElement("br"));
    }

}

<?php

namespace monolitum\frontend;

use monolitum\core\MObject;
use monolitum\frontend\html\HtmlElement;

interface Renderable extends MObject
{

    /**
     * @param HtmlElement $element
     */
    function renderTo(HtmlElement $element): void;

}

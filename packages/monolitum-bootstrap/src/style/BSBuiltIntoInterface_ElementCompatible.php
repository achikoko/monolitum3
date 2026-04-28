<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\html\HtmlElement;

interface BSBuiltIntoInterface_ElementCompatible
{

    public function buildIntoElement(HtmlElement $element, bool $inverted = false): void;

}

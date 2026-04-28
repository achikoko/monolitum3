<?php

namespace monolitum\frontend;

use monolitum\core\ExplicitAcceptChildNode;
use monolitum\frontend\html\HtmlElement;

interface HtmlElementNodeExtension_ElementCompatible extends ExplicitAcceptChildNode
{

    public function buildIntoElement(HtmlElement $element, bool $inverted = false): void;

    public function buildIntoElementResponsive(HtmlElement $component, ?string $breakpoint, bool $inverted = false): void;

}

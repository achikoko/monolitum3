<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;

interface BSBuiltIntoInterface
{

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void;

}

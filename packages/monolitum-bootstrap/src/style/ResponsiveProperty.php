<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;

interface ResponsiveProperty
{

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void;

    public function getValue(bool $inverted = false): string;

}

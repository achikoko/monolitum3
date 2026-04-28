<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\frontend\HtmlElementNodeExtension_ElementCompatible;

class BSJustifyContent extends HtmlElementNodeExtension implements ResponsiveProperty, HtmlElementNodeExtension_ElementCompatible
{

    private function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function start(): BSJustifyContent
    {
        return new BSJustifyContent("start");
    }

    public static function center(): BSJustifyContent
    {
        return new BSJustifyContent("center");
    }

    public static function end(): BSJustifyContent
    {
        return new BSJustifyContent("end");
    }

    public static function between(): BSJustifyContent
    {
        return new BSJustifyContent("between");
    }

    public static function around(): BSJustifyContent
    {
        return new BSJustifyContent("around");
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("justify-content-" . $this->getValue($inverted));
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("justify-content-" . $breakpoint . "-" . $this->getValue($inverted));
    }

    public function buildIntoElement(HtmlElement $element, bool $inverted = false): void
    {
        $element->addClass("justify-content-" . $this->getValue($inverted));
    }

    public function buildIntoElementResponsive(HtmlElement $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("justify-content-" . $breakpoint . "-" . $this->getValue($inverted));
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

    public function getValue(bool $inverted = false): string
    {
        return $this->value;
    }

}

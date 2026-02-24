<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSAlignItems extends HtmlElementNodeExtension implements \monolitum\bootstrap\style\ResponsiveProperty
{

    private function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function start(): BSAlignItems
    {
        return new BSAlignItems("start");
    }

    public static function center(): BSAlignItems
    {
        return new BSAlignItems("center");
    }

    public static function end(): BSAlignItems
    {
        return new BSAlignItems("end");
    }

    public static function baseline(): BSAlignItems
    {
        return new BSAlignItems("baseline");
    }

    public static function stretch(): BSAlignItems
    {
        return new BSAlignItems("stretch");
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("align-items-" . $this->getValue($inverted));
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("align-items-" . $breakpoint . "-" . $this->getValue($inverted));
    }

    public function getValue(bool $inverted = false): string
    {
        return $this->value;
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

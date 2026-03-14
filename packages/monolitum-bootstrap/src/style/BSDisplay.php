<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSDisplay extends HtmlElementNodeExtension implements BSBuiltIntoInterface, ResponsiveProperty
{

    function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function none(): BSDisplay
    {
        return new BSDisplay("none");
    }

    public static function inline(): BSDisplay
    {
        return new BSDisplay("inline");
    }

    public static function inline_block(): BSDisplay
    {
        return new BSDisplay("inline-block");
    }

    public static function block(): BSDisplay
    {
        return new BSDisplay("block");
    }

    public static function grid(): BSDisplay
    {
        return new BSDisplay("grid");
    }

    public static function table(): BSDisplay
    {
        return new BSDisplay("table");
    }

    public static function table_cell(): BSDisplay
    {
        return new BSDisplay("table-cell");
    }

    public static function table_row(): BSDisplay
    {
        return new BSDisplay("table-row");
    }

    public static function flex(): BSFlex
    {
        return new BSFlex("flex");
    }

    public static function inline_flex(): BSFlex
    {
        return new BSFlex("inline-flex");
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("d-" . $this->getValue($inverted));
    }

    public function getValue(bool $inverted = false): string
    {
        return $this->value;
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("d-" . $breakpoint . "-" . $this->getValue($inverted));
    }
}

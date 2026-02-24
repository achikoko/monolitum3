<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSTextAlign extends HtmlElementNodeExtension implements ResponsiveProperty, BSBuiltIntoInterface
{

    public function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function start(): static
    {
        return new self("start");
    }

    public static function center(): static{
        return new self("center");
    }

    public static function end(): static{
        return new self("end");
    }

    public function getValue(bool $inverted = false): string
    {
        return $this->value;
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("text-" . $this->value);
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("text-" . $breakpoint . "-" . $this->value);
    }

}

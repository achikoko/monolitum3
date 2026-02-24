<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSColSpan extends HtmlElementNodeExtension implements BSBuiltIntoInterface, ResponsiveProperty
{

    private function __construct(private readonly int $value)
    {
        parent::__construct();
        assert(1 <= $value && $value <= 12, "Col span should be between 1 and 12");
    }

    public static function of(int $value): static
    {
        return new BSColSpan($value);
    }

    /**
     * @return int
     */
    public function getValue(bool $inverted = false): string
    {
        return $inverted ? 12 - $this->value : $this->value;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("col-" . $this->getValue($inverted));
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("col-" . $breakpoint . "-" . $this->getValue($inverted));
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

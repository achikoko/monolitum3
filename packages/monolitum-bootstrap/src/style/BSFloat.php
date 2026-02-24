<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSFloat extends HtmlElementNodeExtension implements ResponsiveProperty
{

    public function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function start(): static
    {
        return new self("start");
    }

    public static function end(): static {
        return new self("end");
    }

    public static function right(): static {
        return new self("right");
    }

    public static function none(): static {
        return new self("none");
    }

    public static function left(): static {
        return new self("left");
    }

    public function getValue(bool $inverted = false): string
    {
        return $inverted ?
            (
                $this->value === "right" ?
                    "left" :
                    ($this->value === "left" ?
                        "right" :
                        $this->value)
            )
            : $this->value;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("float-" . $this->getValue($inverted));
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        $component->addClass("float-" . $breakpoint . "-" . $this->getValue($inverted));
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

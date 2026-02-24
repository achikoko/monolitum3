<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSColor;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNodeExtension;

class BSBackground extends HtmlElementNodeExtension
{

    private function __construct(private readonly BSColor $color, private readonly bool $subtle, private readonly bool $gradient)
    {
        parent::__construct();
    }

    public static function of(BSColor $color, bool $subtle = false, bool $gradient = false): static
    {
        return new self($color, $subtle, $gradient);
    }

    public function apply(): void
    {
        $txt = "bg-" . $this->color->getValue();
        if($this->subtle)
            $txt .= "-subtle";
        $this->getElementComponent()->addClass($txt);
        if($this->gradient)
            $this->getElementComponent()->addClass("bs-gradient");
    }

    public function getValue(bool $inverted = false): string
    {
        throw new DevPanic("NO");
    }
}

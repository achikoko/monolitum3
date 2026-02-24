<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSSize;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSBounds extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    private ?BSSize $width = null;
    private ?BSSize $height = null;

    public static function of(): static
    {
        return new BSBounds();
    }

    public function width(BSSize $size): self
    {
        $this->width = $size;
        return $this;
    }

    public function height(BSSize $size): self
    {
        $this->height = $size;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {

        if($this->width !== null){
            $component->setClass("bs_width", "w-" . $this->width->getValue());
        }

        if($this->height !== null){
            $component->setClass("bs_height", "h-" . $this->height->getValue());
        }

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }
}

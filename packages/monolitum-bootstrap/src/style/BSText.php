<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSColor;
use monolitum\bootstrap\values\BSWeight;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSText extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    private ?int $size = null;
    private ?BSWeight $weight = null;
    private ?bool $italic = null;
    private ?BSColor $color = null;
    private ?BSColor $backgroundColor = null;
    private ?string $wrapBreakTruncate = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function of(): static
    {
        return new static();
    }

    public function size(int $sizeNum): self
    {
        assert(1 <= $sizeNum && $sizeNum <= 6, "Size should be between 1 and 6");
        $this->size = $sizeNum;
        return $this;
    }

    public function weight(BSWeight $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function italic(bool $italic = true): self
    {
        $this->italic = $italic;
        return $this;
    }

    /**
     * @param BSColor $color
     * @return BSText
     */
    public function color(BSColor $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param BSColor $color
     * @return BSText
     */
    public function bgColor(BSColor $color): self
    {
        $this->backgroundColor = $color;
        return $this;
    }

    public function textWrap(): self
    {
        $this->wrapBreakTruncate = "wrap";
        return $this;
    }


    public function textBreak(): self
    {
        $this->wrapBreakTruncate = "break";
        return $this;
    }

    public function textNoWrap(): self
    {
        $this->wrapBreakTruncate = "nowrap";
        return $this;
    }

    public function textTruncate(): self
    {
        $this->wrapBreakTruncate = "truncate";
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        if($this->size !== null)
            $component->setClass("bs_size", "fs-" . $this->size);

        if($this->weight !== null)
            $component->setClass("bs_weight", "fw-" . $this->weight->getValue());

        if($this->italic !== null) {
            if ($this->italic) {
                $component->setClass("bs_italic", "fst-italic");
            } else {
                $component->setClass("bs_italic", "fst-normal");
            }
        }

        if($this->color !== null)
            $component->addClass("text-" . $this->color->getValue());

        if($this->backgroundColor !== null)
            $component->addClass("text-bg-" . $this->backgroundColor->getValue());

        if($this->wrapBreakTruncate !== null){
            $component->addClass("text-" . $this->wrapBreakTruncate);
        }

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

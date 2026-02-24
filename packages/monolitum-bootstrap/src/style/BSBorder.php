<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSColor;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSBorder extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    private ?string $which = null;

    private ?BSColor $color = null;

    private ?int $width = null;

    private ?string $rounded = null;

    private ?int $rounded_size = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $size
     * @return $this
     */
    public function width(int $size): self
    {
        $this->width = $size;
        return $this;
    }

    /**
     * @param BSColor $color
     * @return $this
     */
    public function color(BSColor $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function rounded(int $size=1): self
    {
        $this->rounded_size = $size;
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_top(): self
    {
        $this->rounded = "top";
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_bottom(): self
    {
        $this->rounded = "bottom";
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_start(): self
    {
        $this->rounded = "start";
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_end(): self
    {
        $this->rounded = "end";
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_circle(): self
    {
        $this->rounded = "circle";
        return $this;
    }

    /**
     * @return $this
     */
    public function rounded_pill(): self
    {
        $this->rounded = "end";
        return $this;
    }

    public static function all(int $size=1): static
    {
        $border = new BSBorder();
        $border->width=$size;
        return $border;
    }

    public static function top(int $size=1): static
    {
        $border = new BSBorder();
        $border->which="top";
        $border->width=$size;
        return $border;
    }

    public static function bottom(int $size=1): static
    {
        $border = new BSBorder();
        $border->which="bottom";
        $border->width=$size;
        return $border;
    }

    public static function start(int $size=1): static
    {
        $border = new BSBorder();
        $border->which="start";
        $border->width=$size;
        return $border;
    }

    /**
     * @param $size int from 1 to 6
     * @param $subtractive boolean
     * @return BSBorder
     */
    public static function end(int $size=1): static
    {
        $border = new BSBorder();
        $border->which="end";
        $border->width=$size;
        return $border;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        if($this->width !== null){
            if($this->width == 0)
                $component->addClass("border-0");
            else{
                $component->addClass("border", "border-" . $this->width);
            }
        }else{
            $component->addClass("border");
        }

        if($this->which !== null){
            $component->addClass("border-" . $this->which);
        }

        if($this->color !== null){
            $component->addClass("border-" . $this->color->getValue());
        }

        if($this->rounded !== null){
            $component->addClass("rounded-" . $this->rounded);
        }else if($this->rounded_size !== null){
            $component->addClass("rounded-" . $this->rounded_size);
        }

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }
}

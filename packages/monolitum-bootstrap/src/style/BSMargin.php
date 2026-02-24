<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSAxis;
use monolitum\bootstrap\values\BSBound;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSMargin extends HtmlElementNodeExtension implements ResponsiveProperty
{

    private function __construct(private readonly ?int $size, private readonly BSBound|BSAxis|null $bound)
    {
        parent::__construct();
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargin
     */
    public static function all(int $size): BSMargin
    {
        return new self($size, null);
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public static function top(?int $size = null): BSMargin
    {
        return new self($size, BSBound::top());
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public static function bottom(?int $size = null): BSMargin
    {
        return new self($size, BSBound::bottom());
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public static function left(?int $size = null): BSMargin
    {
        return new self($size, BSBound::left());
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public static function right(?int $size = null): BSMargin
    {
        return new self($size, BSBound::right());
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargin
     */
    public static function x(int $size): BSMargin
    {
        return new self($size, BSAxis::x());
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargin
     */
    public static function y(int $size): BSMargin
    {
        return new self($size, BSAxis::y());
    }

    public static function autoX(): BSMargin
    {
        return new self(null, BSAxis::x());
    }

    public static function autoY(): BSMargin
    {
        return new self(null, BSAxis::y());
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        if ($breakpoint != null) {

            if ($this->bound != null) {
                if ($this->size == null){
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-" . $breakpoint . "-auto");
                } else if ($this->size < 0){
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-" . $breakpoint . "-n" . (-$this->size));
                } else {
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-" . $breakpoint . "-" . $this->size);
                }
            }else{
                $this->getElementComponent()->addClass("m-" . $breakpoint . "-" . $this->size);
            }

        } else {
            if ($this->bound != null) {
                if ($this->size == null) {
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-auto");
                } else if ($this->size < 0){
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-n" . (-$this->size));
                }else {
                    $this->getElementComponent()->addClass("m" . $this->bound->getValue() . "-" . $this->size);
                }
            }else{
                $this->getElementComponent()->addClass("m-" . $this->size);
            }
        }
    }

    public function getValue(bool $inverted = false): string
    {
        throw new DevPanic("NO");
    }

    public function apply(): void
    {
        $this->buildIntoResponsive($this->getElementComponent(), null);
    }
}

<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSAxis;
use monolitum\bootstrap\values\BSBound;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSPadding extends HtmlElementNodeExtension implements ResponsiveProperty
{

    private function __construct(private readonly ?int $size, private readonly BSBound|BSAxis|null $bound)
    {
        parent::__construct();
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function all(int $size): BSPadding
    {
        return new self($size, null);
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function top(int $size): BSPadding
    {
        return new self($size, BSBound::top());
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function bottom(int $size): BSPadding
    {
        return new self($size, BSBound::bottom());
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function left(int $size): BSPadding
    {
        return new self($size, BSBound::left());
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function right(int $size): BSPadding
    {
        return new self($size, BSBound::right());
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function x(int $size): BSPadding
    {
        return new self($size, BSAxis::x());
    }

    /**
     * @param int $size from 0 to 6
     * @return BSPadding
     */
    public static function y(int $size): BSPadding
    {
        return new self($size, BSAxis::y());
    }

    public function apply(): void
    {
        $this->buildIntoResponsive($this->getElementComponent(), null);
    }

    public function buildIntoResponsive(HtmlElementNode $component, ?string $breakpoint, bool $inverted = false): void
    {
        if ($breakpoint != null) {

            if ($this->bound != null) {
                $component->addClass("p" . $this->bound->getValue() . "-" . $breakpoint . "-" . $this->size);
            }else{
                $component->addClass("p-" . $breakpoint . "-" . $this->size);
            }

        } else {
            if ($this->bound != null) {
                $component->addClass("p" . $this->bound->getValue() . "-" . $this->size);
            }else{
                $component->addClass("p-" . $this->size);
            }
        }
    }

    public function getValue(bool $inverted = false): string
    {
        throw new DevPanic("NO");
    }
}

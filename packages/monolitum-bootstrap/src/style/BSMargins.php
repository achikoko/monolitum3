<?php

namespace monolitum\bootstrap\style;

use monolitum\bootstrap\values\BSAxis;
use monolitum\bootstrap\values\BSBound;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSMargins extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    private array $margins = [];

    public static function of(): static
    {
        return new BSMargins();
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargins
     */
    public function all(int $size): self
    {
        $this->margins[] = BSMargin::all($size);
        return $this;
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public function top(?int $size = null): self
    {
        $this->margins[] = BSMargin::top($size);
        return $this;
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public function bottom(?int $size = null): self
    {
        $this->margins[] = BSMargin::bottom($size);
        return $this;
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public function left(?int $size = null): self
    {
        $this->margins[] = BSMargin::left($size);
        return $this;
    }

    /**
     * @param ?int $size from -6 to 6
     * @return BSMargin
     */
    public function right(?int $size = null): self
    {
        $this->margins[] = BSMargin::right($size);
        return $this;
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargin
     */
    public function x(int $size): self
    {
        $this->margins[] = BSMargin::x($size);
        return $this;
    }

    /**
     * @param int $size from -6 to 6
     * @return BSMargin
     */
    public function y(int $size): self
    {
        $this->margins[] = BSMargin::y($size);
        return $this;
    }

    public function autoX(): self
    {
        $this->margins[] = BSMargin::autoX();
        return $this;
    }

    public function autoY(): self
    {
        $this->margins[] = BSMargin::autoY();
        return $this;
    }

    public function apply(): void
    {
        foreach ($this->margins as $margin) {
            $margin->apply();
        }
    }

    public function _setElementComponent(HtmlElementNode $elementComponent): void
    {
        parent::_setElementComponent($elementComponent);
        foreach ($this->margins as $margin) {
            $margin->_setElementComponent($elementComponent);
        }
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        foreach ($this->margins as $margin) {
            $margin->buildInto($component, $inverted);
        }
    }
}

<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSMarginResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    public function __construct(BSMargin $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSMargin $def = null): static
    {
        return new BSMarginResponsive($def);
    }

    public function sm(BSMargin $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSMargin $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSMargin $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSMargin $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSMargin $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, null, $inverted);
    }

    protected function _buildIntoForBreakpoint(HtmlElementNode $component, string $prefix, ?string $breakpoint, ResponsiveProperty $responsiveProperty, bool $inverted = false): void
    {
        $responsiveProperty->buildIntoResponsive($component, $breakpoint, $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

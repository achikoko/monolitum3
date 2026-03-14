<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSPaddingResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    public function __construct(BSPadding $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSPadding $def = null): static
    {
        return new BSPaddingResponsive($def);
    }

    public function sm(BSPadding $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSPadding $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSPadding $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSPadding $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSPadding $xxl): self
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

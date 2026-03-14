<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSDisplayResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    public function __construct(BSDisplay $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSDisplay $def = null): static
    {
        return new BSDisplayResponsive($def);
    }

    public function sm(BSDisplay $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSDisplay $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSDisplay $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSDisplay $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSDisplay $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "d", $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

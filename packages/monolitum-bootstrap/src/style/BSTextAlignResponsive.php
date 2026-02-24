<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSTextAlignResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    public function __construct(BSTextAlign $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSTextAlign $def = null): static
    {
        return new BSTextAlignResponsive($def);
    }

    public function sm(BSTextAlign $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSTextAlign $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSTextAlign $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSTextAlign $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSTextAlign $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "text", $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSAlignItemsResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    public function __construct(BSAlignItems $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSAlignItems $def = null): static
    {
        return new BSAlignItemsResponsive($def);
    }

    public function sm(BSAlignItems $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSAlignItems $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSAlignItems $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSAlignItems $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSAlignItems $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "align-items",  $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

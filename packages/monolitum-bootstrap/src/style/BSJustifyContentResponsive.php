<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSJustifyContentResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface, BSBuiltIntoInterface_ElementCompatible
{
    use ResponsiveTrait;

    public function __construct(BSJustifyContent $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSJustifyContent $def = null): static
    {
        return new BSJustifyContentResponsive($def);
    }

    public function sm(BSJustifyContent $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSJustifyContent $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSJustifyContent $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSJustifyContent $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSJustifyContent $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "justify-content",  $inverted);
    }

    public function buildIntoElement(HtmlElement $element, bool $inverted = false): void
    {
        $this->_buildInto($element, "justify-content",  $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

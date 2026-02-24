<?php

namespace monolitum\bootstrap\style;


use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSFloatResponsive extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{
    use ResponsiveTrait;

    /**
     * @param BSFloat $def
     */
    public function __construct(BSFloat $def)
    {
        parent::__construct();
        $this->def = $def;
    }

    public static function xs(BSFloat $def = null): static
    {
        return new BSFloatResponsive($def);
    }

    public function sm(BSFloat $sm): self
    {
        $this->sm = $sm;
        return $this;
    }

    public function md(BSFloat $md): self
    {
        $this->md = $md;
        return $this;
    }

    public function lg(BSFloat $lg): self
    {
        $this->lg = $lg;
        return $this;
    }

    public function xl(BSFloat $xl): self
    {
        $this->xl = $xl;
        return $this;
    }

    public function xxl(BSFloat $xxl): self
    {
        $this->xxl = $xxl;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $this->_buildInto($component, "float",  $inverted);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}

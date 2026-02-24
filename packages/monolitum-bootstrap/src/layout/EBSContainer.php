<?php

namespace monolitum\bootstrap\layout;

use monolitum\bootstrap\values\BSConstants;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class EBSContainer extends HtmlElementNode
{

    private ?string $breakpoint = null;

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("div"), $builder);
    }

    public function setBreakpoint($breakpoint): self
    {
        $this->breakpoint = $breakpoint;
        return $this;
    }

    public function xs(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_XS;
        return $this;
    }

    public function sm(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_SM;
        return $this;
    }

    public function md(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_MD;
        return $this;
    }

    public function lg(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_LG;
        return $this;
    }

    public function xl(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_XL;
        return $this;
    }

    public function xxl(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_XXL;
        return $this;
    }

    public function fluid(): self
    {
        $this->breakpoint = BSConstants::BREAKPOINT_FLUID;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        if ($this->breakpoint == BSConstants::BREAKPOINT_XS || $this->breakpoint == null) {
            $this->addClass("container");
        } else if ($this->breakpoint == BSConstants::BREAKPOINT_FLUID) {
            $this->addClass("container-fluid");
        } else {
            $this->addClass("container-" . $this->breakpoint);
        }

        parent::onAfterBuild();
    }

}

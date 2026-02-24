<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSCol;
use monolitum\bootstrap\style\BSColSpan;
use monolitum\bootstrap\style\BSColSpanResponsive;
use monolitum\frontend\component\Div;

class EBSCol extends Div
{
    private BSCol $layout;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSCol::of();
    }

    public function span(int|BSColSpan|BSColSpanResponsive $gutter): self
    {
        $this->layout->span($gutter);
        return $this;
    }

    protected function onAfterBuild(): void
    {
        $this->layout->buildInto($this);
        parent::onAfterBuild();
    }
}

<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSVStack;
use monolitum\frontend\component\Div;

class EBSVStack extends Div
{
    private BSVStack $layout;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSVStack::of();
    }

    public function gap(int $gap): self
    {
        $this->layout->gap($gap);
        return $this;
    }

    protected function onAfterBuild(): void
    {
        $this->layout->buildInto($this);
        parent::onAfterBuild();
    }
}

<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSHStack;
use monolitum\frontend\component\Div;

class EBSHStack extends Div
{
    private BSHStack $layout;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSHStack::of();
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

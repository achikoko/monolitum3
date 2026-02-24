<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSDisplay;
use monolitum\bootstrap\style\BSFlex;
use monolitum\frontend\component\Div;

class EBSFlex extends Div
{
    private BSFlex $layout;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSDisplay::flex();
    }

    protected function onAfterBuild(): void
    {
        $this->layout->buildInto($this);
        parent::onAfterBuild();
    }
}

<?php

namespace monolitum\bootstrap\layout;

use monolitum\frontend\component\Div;

class EBSHStack extends Div
{
    private \monolitum\bootstrap\style\BSHStack $layout;

    public function __construct()
    {
        parent::__construct();
        $this->layout = \monolitum\bootstrap\style\BSHStack::of();
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

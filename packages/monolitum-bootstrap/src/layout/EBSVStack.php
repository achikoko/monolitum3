<?php

namespace monolitum\bootstrap\layout;

use monolitum\frontend\component\Div;

class EBSVStack extends Div
{
    private \monolitum\bootstrap\style\BSVStack $layout;

    public function __construct()
    {
        parent::__construct();
        $this->layout = \monolitum\bootstrap\style\BSVStack::of();
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

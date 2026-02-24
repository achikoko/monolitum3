<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSRow;
use monolitum\frontend\component\Div;

class EBSRow extends Div
{
    private BSRow $layout;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSRow::of();
    }

    public function gx(int $gutter): self
    {
        $this->layout->gx($gutter);
        return $this;
    }

    public function gy(int $gutter): self
    {
        $this->layout->gy($gutter);
        return $this;
    }

    protected function onAfterBuild(): void
    {
        $this->layout->buildInto($this);
        parent::onAfterBuild();
    }
}

<?php
namespace monolitum\frontend\form;

class AttrExt_Form_DateTime extends AttrExt_Form
{

    private bool $isLongAway = false;

    public function isLongAway(bool $isLongAway = true): self
    {
        $this->isLongAway = $isLongAway;
        return $this;
    }

    public function getIsLongAway(): bool
    {
        return $this->isLongAway;
    }

}


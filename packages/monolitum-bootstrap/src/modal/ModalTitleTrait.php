<?php

namespace monolitum\bootstrap\modal;

use monolitum\frontend\Renderable;

trait ModalTitleTrait
{

    private ?string $title = null;

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    abstract public function buildRenderable(Renderable $active): Renderable;

}

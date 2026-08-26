<?php

namespace monolitum\bootstrap\modal;

use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

trait ModalTitleTrait
{

    private ?string $title = null;

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    abstract public function buildRenderable(Renderable_Node|Renderable|string|TS|array $renderable): Renderable_Node|Renderable;

}

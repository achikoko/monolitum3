<?php

namespace monolitum\bootstrap\modal;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\LinkHook;
use monolitum\frontend\Renderable_Node;

class ModalToggleLinkHook implements LinkHook
{

    private HasModalId $modal;

    function __construct(HasModalId $modal)
    {
        $this->modal = $modal;
    }

    public function buildLinkHook(Renderable_Node $component, HtmlElement $element = null): void
    {

    }

    public function renderLinkHook(Renderable_Node $component, HtmlElement $element): void
    {
        $element->setAttribute("data-bs-toggle", "modal");
        $element->setAttribute("href", "#" . $this->modal->getModalId());
    }
}

<?php

namespace monolitum\bootstrap\modal;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\LinkHook;
use monolitum\frontend\LinkHookMode;
use monolitum\frontend\Renderable_Node;

class ModalToggleLinkHook implements LinkHook
{

    private HasModalId $modal;

    function __construct(HasModalId $modal)
    {
        $this->modal = $modal;
    }

    public function buildLinkHook(Renderable_Node $triggerComponent, LinkHookMode $preferredMode, array $extra, ?HtmlElement $element = null): ?LinkHookMode
    {
        return LinkHookMode::MODIFY_RECEIVER;
    }

    public function renderLinkHookIntoElement(Renderable_Node $renderable_Node, array $extra, HtmlElement $element): void
    {
        $element->setAttribute("data-bs-toggle", "modal");
        $element->setAttribute("href", "#" . $this->modal->getModalId());
    }

    public function renderLinkHookIntoJavascript(Renderable_Node $renderable_Node, array $extra): string
    {
        // TODO: Implement renderLinkHookIntoJavascript() method.
        return "";
    }
}

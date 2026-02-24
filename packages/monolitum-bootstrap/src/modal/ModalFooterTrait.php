<?php

namespace monolitum\bootstrap\modal;

use monolitum\core\util\ListUtils;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;

trait ModalFooterTrait
{
    private array $footerElements = [];

    public function appendFooter(Renderable $active, ?int $idx = null): self
    {
        ListUtils::insertAnElementIntoAnArray($this->footerElements, $this->buildRenderable($active), $idx);
        return $this;
    }

    abstract public function buildRenderable(Renderable $active): Renderable;

    private function createModalFooterElement(): ?HtmlElement
    {

        if(count($this->footerElements) > 0){

            $modalFooter = new HtmlElement("div");
            $modalFooter->addClass("modal-footer");

            Renderable_Node::renderRenderedTo(Renderable_Node::valueToRenderable($this->footerElements), $modalFooter);

            return $modalFooter;
        }

        return null;
    }

}

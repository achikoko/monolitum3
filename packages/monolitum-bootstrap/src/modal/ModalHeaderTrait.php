<?php

namespace monolitum\bootstrap\modal;

use monolitum\core\util\ListUtils;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;

trait ModalHeaderTrait
{
    use ModalTitleTrait;

    private array $headerElements = [];

    public function appendHeader(Renderable $active, ?int $idx = null): self
    {
        ListUtils::insertAnElementIntoAnArray($this->headerElements, $this->buildRenderable($active), $idx);
        return $this;
    }

    abstract public function buildRenderable(Renderable $active): Renderable;

    private function createModalHeaderElement(): ?HtmlElement
    {
        if(count($this->headerElements) > 0 || $this->title !== null){

            $modalHeader = new HtmlElement("div");
            $modalHeader->addClass("modal-header");

            if($this->title !== null){

                $modalTitle = new HtmlElement("h1");
                $modalTitle->addClass("modal-title", "fs-5");
                $modalTitle->setContent($this->title);

                $modalHeader->addChildElement($modalTitle);
            }

            Renderable_Node::renderRenderedTo(Renderable_Node::valueToRenderable($this->headerElements), $modalHeader);

            return $modalHeader;
        }

        return null;
    }

}

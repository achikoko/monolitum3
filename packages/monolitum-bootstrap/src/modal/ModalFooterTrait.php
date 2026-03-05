<?php

namespace monolitum\bootstrap\modal;

use Closure;
use monolitum\core\util\ListUtils;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

trait ModalFooterTrait
{
    private array $footerElements = [];

    public function appendFooter(Renderable $active, ?int $idx = null): self
    {
        $this->buildRenderableManually($active, function ($e) use (&$idx) {
            ListUtils::insertAnElementIntoAnArray($this->footerElements, $e, $idx);
            if($idx !== null){$idx++;}
        });
//        ListUtils::insertAnElementIntoAnArray($this->footerElements, $this->buildRenderable($active), $idx);
        return $this;
    }

//    abstract public function buildRenderable(Renderable $active): Renderable;
    abstract function buildRenderableManually(Renderable_Node|Renderable|string|TS|array $renderable, Closure $callback): void;

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

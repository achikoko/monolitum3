<?php

namespace monolitum\bootstrap\modal;

use Closure;
use monolitum\backend\globals\Request_NewId;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

/*
 * <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Modal 1</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Show a second modal and hide this one with the button below.
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-bs-target="#exampleModalToggle2" data-bs-toggle="modal">Open second modal</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="exampleModalToggle2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Modal 2</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Hide this modal and show the first with the button below.
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to first</button>
      </div>
    </div>
  </div>
</div>
<a class="btn btn-primary" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Open first modal</a>
 */

class StaticModal extends Renderable_Node
{

    use ModalHeaderTrait;
    use ModalFooterTrait;
    use ModalIdTrait;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function newLinkToToggleModal(): ModalToggleLinkHook
    {
        return new ModalToggleLinkHook($this);
    }

    protected function onBuild(): void
    {
        $this->modalId = Request_NewId::pushAndGet("modal");
        parent::onBuild();
    }

    public function render(): Renderable|array|null
    {

        $modalContent = new HtmlElement("div");
        $modalContent->addClass("modal-content");

        {

            $modalHeader = $this->createModalHeaderElement();
            if($modalHeader !== null){
                $modalContent->addChildElement($modalHeader);
            }

            $modalBody = new HtmlElement("div");
            $modalBody->addClass("modal-body");

            Renderable_Node::renderRenderedTo($this->renderChildren(), $modalBody);

            $modalContent->addChildElement($modalBody);

            $modalFooter = $this->createModalFooterElement();
            if($modalFooter !== null){
                $modalContent->addChildElement($modalFooter);
            }

        }

        return Rendered::of(self::createModalElement($this->modalId, $modalContent));
    }

    /**
     * @param string $modalId
     * @param Renderable $contentElement
     * @return HtmlElement
     */
    public static function createModalElement(string $modalId, HtmlElement $contentElement): HtmlElement
    {
        $modal = new HtmlElement("div");
        $modal->setId($modalId);
        $modal->addClass("modal", "fade");
        $modal->setAttribute("tabindex", "-1");

        {

            $modalChild = new HtmlElement("div");
            $modalChild->addClass("modal-dialog", "modal-dialog-centered");

            $contentElement->renderTo($modalChild);

            $modal->addChildElement($modalChild);
        }

        return $modal;
    }

}

// <a class="btn btn-primary" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Open first modal</a>

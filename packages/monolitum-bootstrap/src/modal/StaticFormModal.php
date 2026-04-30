<?php

namespace monolitum\bootstrap\modal;

use Closure;
use monolitum\backend\globals\Request_NewId;
use monolitum\backend\params\ParamsManager;
use monolitum\backend\params\Source;
use monolitum\core\Find;
use monolitum\frontend\form\Form;
use monolitum\frontend\form\Form_Validator;
use monolitum\frontend\form\Form_Validator_Anonymous;
use monolitum\frontend\form\Form_Validator_Entity;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\model\AnonymousModel;
use monolitum\model\Entity;
use monolitum\model\Model;

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

class StaticFormModal extends Form implements HasModalId
{

    use ModalHeaderTrait;
    use ModalFooterTrait;
    use ModalIdTrait;

    /**
     * @param Form_Validator|null $validator
     * @param string $formId
     * @param Closure|null $builder
     */
    public function __construct($validator, $formId, ?Closure $builder = null)
    {
        parent::__construct($validator, $formId, $builder);
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
        $formElement = $this->getFormElement();
        if($formElement === null){
            // A form parent exist, create our own
            $modalContent = new HtmlElement("div");
            $modalContent->addClass("modal-content");

            $parent = parent::render();
            $parent->renderTo($modalContent);

            return Rendered::of(StaticModal::createModalElement($this->modalId, $parent));
        }else{
            $formElement->addClass("modal-content");
            return Rendered::of(StaticModal::createModalElement($this->modalId, parent::render()));
        }
    }

    public function renderChildren(): Renderable|array|null
    {

        $modalHeader = $this->createModalHeaderElement();

        $modalBody = new HtmlElement("div");
        $modalBody->addClass("modal-body");

        Renderable_Node::renderRenderedTo(parent::renderChildren(), $modalBody);

        $modalFooter = $this->createModalFooterElement();

        return Rendered::of([
            $modalHeader,
            $modalBody,
            $modalFooter
        ]);
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModel(AnonymousModel|Model|string $model, ?Closure $builder): StaticFormModal
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new StaticFormModal(new Form_Validator_Entity(
            $manager_params,
            $model,
            Source::POST
        ), null, $builder);
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModelAndEntity(AnonymousModel|string $model, Entity $entity, ?Closure $builder): StaticFormModal
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new StaticFormModal((new Form_Validator_Entity(
            $manager_params,
            $model,
            Source::POST
        ))->setCurrentEntity($entity), null, $builder);
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModelAndId(AnonymousModel|string $model, ?string $formId, ?Closure $builder): StaticFormModal
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new StaticFormModal(new Form_Validator_Entity(
            $manager_params,
            $model,
            Source::POST
        ), $formId, $builder);
    }

    /**
     * Creates a Form without validator.
     */
    public static function fromAnonymousModel(?Closure $builder): StaticFormModal
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        $fc = new StaticFormModal(new Form_Validator_Anonymous($manager_params), null, $builder);
        $fc->setAnonymousAttributesNames();
        return $fc;
    }

}

// <a class="btn btn-primary" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Open first modal</a>

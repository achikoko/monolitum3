<?php

namespace monolitum\frontend\form;


use Closure;
use monolitum\backend\globals\Request_NewId;
use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\backend\resources\HrefResolver;
use monolitum\core\Find;
use monolitum\frontend\component\AbstractTextNode;
use monolitum\frontend\html\HtmlElement;

abstract class FormSubmit extends AbstractTextNode
{
    use Trait_Form_Validate_Attrs;

    protected Form $form;

    protected ?string $submitKey = null;

    protected Path|Link|null $link = null;

    protected ?HrefResolver $linkResolver = null;

    private ?Closure $onValidated = null;

    public function __construct(HtmlElement $element, ?Closure $builder = null)
    {
        parent::__construct($element, $builder);
    }

    /**
     * @return string|null
     */
    public function getFinalCustomFormMethod(): ?string
    {
        return $this->form->_getSubmitMethod($this);
    }

    public function getFinalCustomLinkResolver(): ?HrefResolver
    {
        if($this->linkResolver !== null)
            return $this->linkResolver;
        return $this->form->_getSubmitLinkResolver($this);
    }

    /**
     * @return string
     */
    public function getFinalName(): string
    {

        $prefix = $this->form->_getSubmitPrefix($this);
        $action = $this->getSubmitKey();

        return ($prefix !== null ? $prefix : "") .  ($action !== null ? $action : "");
    }

    /**
     * @param Link|Path $link
     */
    public function setLink(Link|Path $link): void
    {
        $this->link = $link;
    }

    public function setOnValidated(?Closure $onValidated): void
    {
        $this->onValidated = $onValidated;
    }

    /**
     * @param string $submitKey
     */
    public function setSubmitKey(string $submitKey): void
    {
        $this->submitKey = $submitKey;
    }

    public function getSubmitKey(): ?string
    {
        return $this->submitKey;
    }

    public function getOnValidated(): ?Closure
    {
        return $this->onValidated;
    }

    /**
     * @param Form_Validator $validator
     * @return bool true if set
     */
    function _setValidateAttrsInto(Form_Validator $validator): bool
    {
        if($this->validate_attrs_hasBeenSet){
            if($this->validate_attrs_all){
                $validator->validate_all_except(...$this->validate_attrs);
            }else{
                $validator->validate_only(...$this->validate_attrs);
            }
            return true;
        }
        return false;
    }

    protected function onBuild(): void
    {
        $this->form = Find::pushAndGet(Form::class);
        $this->form->_registerFormSubmit($this);

        parent::onBuild();
    }

    protected function onAfterBuild(): void
    {
        parent::onAfterBuild();

        if($this->onValidated !== null && $this->submitKey === null){
            // Has own onValidated, but not a submit key
            $this->submitKey = Request_NewId::pushAndGet("formsubmit");
        }

    }

    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Called by the form, when it's just built and validated
     * @return void
     */
    abstract public function onAfterBuildForm(): void;

}

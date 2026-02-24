<?php

namespace monolitum\frontend\form;

use Closure;
use monolitum\backend\globals\Request_NewId;
use monolitum\backend\params\AttrExt_Param;
use monolitum\backend\params\Link;
use monolitum\backend\params\ParamsManager;
use monolitum\backend\params\Path;
use monolitum\backend\resources\HrefResolver;
use monolitum\backend\resources\Request_HrefResolver;
use monolitum\core\Find;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\i18n\TS;
use monolitum\model\AnonymousModel;
use monolitum\model\attr\Attr;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class Form extends Renderable_Node
{
    use Trait_Form_Validate_Attrs;

    private ?Form_Validator $validator;

    /**
     * If flag is true, attributes' names are written as is, without any prefix, so they cannot be identified later.
     * @var bool
     */
    private bool $anonymousAttributesNames = false;

    /**
     * @var array<string, mixed>
     */
    private array $defaultValues = [];

    private ?string $formId;

    /**
     * @var ?Closure(Form $this, string $action)
     */
    private ?Closure $onValidated = null;

    /**
     * Prevents the form to be validated. If this flag is enabled, the form is not validated. (The coming fields are kept, dough)
     * @var bool
     */
    private ?bool $notValidate = null;

    /**
     * Every form field is disabled if this flag is enabled
     * @var bool
     */
    private bool $disabled = false;

    private bool $silentValidation = false;

    private bool $methodGET = false;

    private Path|Link|null $linkOrPath = null;

    private ?HrefResolver $linkResolver = null;

    /**
     * @var array<string, I_Form_Attr>
     */
    private array $formAttrs = [];

    /**
     * @var array<FormSubmit>
     */
    private array $formSubmit = [];

    ///
    /// HIDDEN VALUES
    ///

    /**
     * @var bool|string[]
     */
    private array|bool $copyParams = false;

    /**
     * @var string[]
     */
    private array $removeParams = [];

    /**
     * @var array<string, string>
     */
    private array $addParams = [];

    /**
     * TODO Compute
     * @var array<string, string>
     */
    private array|null $computedParamsAlone = null;

    ///
    /// INTERNAL FIELDS
    ///

    /**
     * @var Form|null
     */
    private ?Form $rootForm = null;

    /**
     * @var HtmlElement
     */
    private HtmlElement $formElement;

    /**
     * @var bool
     */
    private bool $hasNestedForms = false;

    /**
     * @var array<Form>
     */
    private array $nestedForms = [];

    /**
     * The POST had a correct formid value.
     * So execute there are values available and the validation can be executed.
     * @var bool
     */
    private bool $build_isValidating = false;

    private bool $build_overrideSubmitLinks = false;

    /**
     * @var array<string, ValidatedValue>
     */
    protected array $build_displayValidatedValues = [];

    public function __construct(?Form_Validator $validator, ?string $formId, ?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->validator = $validator;
        $this->formId = $formId;
        $this->validator?->_setForm($this);

    }

    /**
     * @param Link|Path $link
     */
    public function setLink(Link|Path $link): void
    {
        $this->linkOrPath = $link;
    }

    public function setMethodGET($setAnonymousSubmission=true): void
    {
        $this->methodGET = true;
        if(is_bool($setAnonymousSubmission))
            $this->anonymousAttributesNames = $setAnonymousSubmission;
    }

    /**
     * @param string $attrString
     * @param mixed $value
     */
    public function setDefaultValue(string $attrString, mixed $value): void
    {
        $this->defaultValues[$attrString] = $value;
    }

    /**
     * @param string ...$attrs
     */
    public function validate_all_except(string ...$attrs): void
    {

        if($this->validator !== null){
            $this->validator->validate_all_except(...$attrs);
        }else{
            throw new DevPanic("Setting attributes to validate is not supported without validator.");
        }

    }

    /**
     * @param string ...$attrs
     */
    public function validate_only(?string ...$attrs): void
    {

        if($this->validator !== null){
            $this->validator->validate_only(...$attrs);
        }else{
            throw new DevPanic("Setting attributes to validate is not supported without validator.");
        }

    }

    /**
     * @param string $attr
     * @param string|TS $errorString
     */
    public function invalidate(string $attr, string|TS $errorString): void
    {
        if($this->validator !== null){
            $this->validator->invalidate($attr, $errorString);
        }else{
            throw new DevPanic("Invalidating attributes is not supported without validator.");
        }
    }

    /**
     * @param ?Entity $currentEntity
     * @return $this
     */
    public function setCurrentEntity(?Entity $currentEntity): void
    {
        if($this->validator !== null){
            if($this->validator instanceof Form_Validator_Entity){
                $this->validator->setCurrentEntity($currentEntity);
            }else{
                throw new DevPanic("Form_Validator_Entity required to set current entity.");
            }
        }else{
            throw new DevPanic("Setting attributes to validate is not supported without validator.");
        }
    }

    /**
     * @param true $anonymousAttributesNames
     */
    public function setAnonymousAttributesNames(bool $anonymousAttributesNames=true): void
    {
        $this->anonymousAttributesNames = $anonymousAttributesNames;
    }

    /**
     * @param callable $onValidated void(Form $this, string $action)
     */
    public function setOnValidated(Closure $onValidated): void
    {
        $this->onValidated = $onValidated;
    }

    public function setSilentValidation(bool $silentValidation=true): void
    {
        $this->silentValidation = $silentValidation;
    }

    /**
     * @return bool
     */
    public function isSilentValidation(): bool
    {
        return $this->silentValidation;
    }

    public function setNotValidate(bool $notValidate=true): void
    {
        $this->notValidate = $notValidate;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled = true): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->formId;
    }

    /**
     * @param string|Attr $attrId
     * @return string|Attr
     */
    function _getAttr(Attr|string $attrId): Attr|string
    {
        if($this->validator !== null)
            return $this->validator->getAttr($attrId);
        return $attrId;
    }

    /**
     * Retrieves the name of the form control (the one on the POST return)
     * @param string|Attr $attr
     * @return string
     */
    function _getFullFieldName(Attr|string $attr): string
    {
        // Append the form Id if it is necessary to be appended

        if($attr instanceof Attr){

            /** @var AttrExt_Param|null $attrExt_Param */
            $attrExt_Param = $attr->findExtension(AttrExt_Param::class);

            if($attrExt_Param != null){
                $attrId = $attrExt_Param->getName();
            }else{
                $attrId = $attr->getId();
            }

        }else {
            $attrId = $attr;
        }

//        if($this->hasNestedForms || $this->rootForm !== null)
        if(!$this->anonymousAttributesNames)
            $attrId = $this->formId . "__" . $attrId;

        return $attrId;
    }

    /**
     * Returns the prefix for the attribute "name" of the input submit element.
     * It must contain the formid.
     * @param FormSubmit $form_submit
     * @return string
     */
    function _getSubmitPrefix(FormSubmit $form_submit): ?string
    {
        if($this->anonymousAttributesNames)
            return null;
        return $this->formId . "_submit__";
    }

    /**
     *
     * @param FormSubmit $form_submit
     * @return string|null
     */
    function _getSubmitMethod(FormSubmit $form_submit): ?string
    {
//        if($this->hasNestedForms || $this->rootForm !== null)
//            return $this->methodGET ? "get" : "post";
        // Method is defined in the root form
        return null;
    }

    /**
     * @param FormSubmit $form_submit
     * @return HrefResolver|null
     */
    function _getSubmitLinkResolver(FormSubmit $form_submit): ?HrefResolver
    {
        if(($this->rootForm !== null || $this->build_overrideSubmitLinks) && $this->linkResolver !== null)
            return $this->linkResolver;
        return null;
    }

    /**
     * @return string
     */
    public function _getValidatePrefix(): ?string
    {
//        if($this->hasNestedForms || $this->rootForm !== null)
        if(!$this->anonymousAttributesNames)
            return $this->formId . "__";
        return null;
    }

    /**
     * @return bool
     */
    public function isValidating(): bool
    {
        return $this->build_isValidating;
    }

    /**
     * Read the value from the external source, validate it and return it.
     * @param string|Attr $attr
     * @return ValidatedValue
     */
    public function getValidatedValue(Attr|string $attr): ?ValidatedValue
    {

        if(!$this->build_isValidating)
            return null;

        if($this->validator === null){
            return new ValidatedValue(false);
        }else{
            return $this->validator->getValidatedValue($attr);
        }

    }


    /**
     * Return the value that must be displayed on the rendering screen.
     * For example, if not validating any form, the value of the entity being edited.
     * Or if user wrote a well formatted but not valid value, that value.
     * Or if user not put any value and dev set a default value, that value.
     * @param string|Attr $attr
     * @return ValidatedValue
     */
    public function getDisplayValue(Attr|string $attr): ValidatedValue
    {

        if($this->validator !== null){
            if($this->isValidating()){
                if($this->validator->isAttrInValidateList($attr)){
                    $validatedValue = $this->validator->getValidatedValue($attr);
                    if($validatedValue->isValid() || $validatedValue->isWellFormat())
                        return $validatedValue;
                }
            }

            if(key_exists($attr->getId(), $this->defaultValues)){
                $validatedValue = new ValidatedValue(true, true, $this->defaultValues[$attr->getId()]);
            }else{
                $validatedValue = $this->validator->getDefaultValue($attr);
            }

            if($validatedValue->isValid())
                return $validatedValue;

        }

        if(key_exists($attr->getId(), $this->defaultValues)){
            return new ValidatedValue(true, true, $this->defaultValues[$attr->getId()]);
        }else{
            return new ValidatedValue(false);
        }

    }

    /**
     * @return Form_Validator|null
     */
    public function getValidator(): ?Form_Validator
    {
        return $this->validator;
    }

    /**
     * @param Form $form
     * @param string $key
     * @param string $value
     * @return HtmlElement
     */
    public function createHiddenInput(Form $form, string $key, string $value): ?HtmlElement
    {
        $exists = false;
        foreach ($form->formAttrs as $attr => $formAttr) {
            if ($key === $attr) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $elem = new HtmlElement("input");
            $elem->setAttribute("type", "hidden");
            if (!$this->anonymousAttributesNames && $form->formId !== null) {
                $elem->setAttribute("name", $form->formId . "__" . $key);
            } else {
                $elem->setAttribute("name", $key);
            }
            $elem->setAttribute("value", $value);
            return $elem;
        }
        return null;
    }

    /**
     * Called from nested Forms, in order this root Form to manage all ids.
     * @param Form $form
     * @return void
     */
    function _registerNestedForm(Form $form): void
    {
        assert($this->rootForm === null);
        $this->hasNestedForms = true;
        $this->nestedForms[] = $form;
    }

    /**
     * Called from form fields telling that the attribute it handles is present.
     * @param I_Form_Attr $formAttr
     * @param Attr $attr
     * @return void
     */
    function _registerFormAttr(I_Form_Attr $formAttr, Attr $attr): void
    {
        $this->formAttrs[$attr->getId()] = $formAttr;
    }

    /**
     * Called from form fields telling that the attribute it handles is present.
     * @param FormSubmit $formSubmit
     * @return void
     */
    function _registerFormSubmit(FormSubmit $formSubmit): void
    {
        $this->formSubmit[] = $formSubmit;
    }

    /**
     * @param Entity $entity
     * @return void
     */
    public function writeValidValuesOn(Entity $entity): void
    {

        if($this->validator !== null){
            $this->validator->writeValidValuesOn($entity);
        }else{
            throw new DevPanic("Writing values to an entity is not supported without validator");
        }

    }

    /**
     * @return bool
     */
    public function isAllValid(): bool
    {
        if($this->validator !== null){
            return $this->validator->isAllValid();
        }else{
            throw new DevPanic("Asking if all is valid is not supported without a validator defined.");
        }
    }


    protected function onBuild(): void
    {
        // Generate an ID to identify the submission of this form if not exist

        if($this->formId === null)
            $this->formId = Request_NewId::pushAndGet("form");

        // Find root form before all
        // If there are nested forms, they will find me and the real root form

        /** @var Form $parentForm */
        $parentForm = Find::pushAndGetFrom(Form::class, $this->getParent(), true, true);
        if($parentForm !== null){
            $this->rootForm = $parentForm->rootForm;
            if($this->rootForm == null)
                $this->rootForm = $parentForm;
            $this->rootForm->_registerNestedForm($this);
        }

        $validatedValueKey = $this->getSubmissionKey();

        if($validatedValueKey !== null && $validatedValueKey->isValid()) {
            $this->build_isValidating = true;
        }

        parent::onBuild(); // TODO: Change the autogenerated stub
    }

    protected function onAfterBuild(): void
    {

        if($this->isValidating() && !$this->notValidate){

            $validatedValueKey = $this->getSubmissionKey();

            $action = $validatedValueKey->getValue();
            if(is_string($action) && !empty($action)){

                $submitFound = null;

                // Find submit button that triggered this action
                foreach ($this->formSubmit as $submit){
                    $submitAction = $submit->getSubmitKey();
                    if($submitAction === $action){
                        $submitFound = $submit;
                    }
                }

                if($submitFound !== null){
                    $this->setValidateAttrsIntoValidator($submitFound);
                }

                $this->validator->_validateAll();

                if($submitFound !== null){
                    $onValidated = $submitFound->getOnValidated();
                    if($onValidated !== null){
                        $onValidated($this, $action);
                    }
                }

                // Execute validation callback
                if($this->onValidated != null){

                    $callback = $this->onValidated;
                    $callback($this, $action);

                }

            }else{

                $this->setValidateAttrsIntoValidator(null);

                $this->validator->_validateAll();

                // Execute validation callback
                if($this->onValidated !== null){

                    $callback = $this->onValidated;
                    $callback($this);

                }

            }

        }

        if($this->linkOrPath !== null){
            $active = new Request_HrefResolver($this->linkOrPath);
            $active->setParamsAlone();
            Monolitum::getInstance()->push($active);
            $this->linkResolver = $active->getHrefResolver();
        }

        if($this->rootForm === null){
            // Create form
            $this->formElement = new HtmlElement("form");
            $this->formElement->setAttribute("enctype", "multipart/form-data");

            if($this->methodGET)
                $this->formElement->setAttribute("method", "get");
            else
                $this->formElement->setAttribute("method", "post");

            if($this->hasNestedForms){

                if($this->linkResolver !== null){
                    // Very likely will be different
                    $this->build_overrideSubmitLinks = true;
                }else{
                    foreach ($this->nestedForms as $form) {
                        $otherLinkResolver = $form->linkResolver;
                        if ($otherLinkResolver !== null) {
                            $this->build_overrideSubmitLinks = true;
                            break;
                        }
                    }
                }

            }

        }

        foreach ($this->formAttrs as $value){
            $value->onAfterBuildForm();
        }

        foreach ($this->formSubmit as $value){
            $value->onAfterBuildForm();
        }


    }

    /**
     * After "afterBuildNode()" this attribute may or may not be set to the element to render the form. If not, then a parent form exist.
     * @return HtmlElement|null
     */
    public function getFormElement(): ?HtmlElement
    {
        return $this->formElement;
    }

    protected function onExecute(): void
    {

        // Append to the beginning the path parameters
        if($this->linkResolver !== null){
            $this->formElement->setAttribute("action", $this->linkResolver->resolve());

            $paramsAlone = $this->linkResolver->getAloneParamValues();
            if(is_array($paramsAlone)){
                foreach ($paramsAlone as $key => $value) {
                    $input = $this->createHiddenInput($this, $key, $value);
                    if ($input !== null)
                        $this->formElement->addChildElement($input);
                }
            }

        }

        if($this->computedParamsAlone !== null) {
            foreach ($this->computedParamsAlone as $key => $value) {
                $input = $this->createHiddenInput($this, $key, $value);
                if ($input !== null)
                    $this->formElement->addChildElement($input);
            }
        }

        foreach ($this->nestedForms as $form){
            $computedParamsAlone = $form->computedParamsAlone;
            if($computedParamsAlone !== null){
                foreach($computedParamsAlone as $key => $value){
                    $input = $this->createHiddenInput($form, $key, $value);
                    if($input !== null)
                        $this->formElement->addChildElement($input);
                }

            }
        }

        parent::onExecute();
    }

    public function render(): Renderable|array|null
    {
        $parentRender = parent::render();
        if($this->formElement !== null){
            Renderable_Node::renderRenderedTo($parentRender, $this->formElement);
            return Rendered::of($this->formElement);
        }else{
            return $parentRender;
        }
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModel(AnonymousModel|Model|string $model, ?Closure $builder): Form
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new Form(new Form_Validator_Entity(
            $manager_params,
            $model,
            true
        ), null, $builder);
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModelAndEntity(Model|string $model, Entity $entity, ?Closure $builder): Form
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new Form((new Form_Validator_Entity(
            $manager_params,
            $model,
            true
        ))->setCurrentEntity($entity), null, $builder);
    }

    /**
     * Creates a Form using Manager_Params as provider and a Model as model.
     */
    public static function fromModelAndId(Model|string $model, string $formId, ?Closure $builder): Form
    {
        /** @var ParamsManager $manager_params */
        $manager_params = Find::pushAndGet(ParamsManager::class);
        return new Form(new Form_Validator_Entity(
            $manager_params,
            $model,
            true
        ), $formId, $builder);
    }

    /**
     * Creates a Form without validator.
     */
    public static function fromAnonymous(?Closure $builder): Form
    {
        $fc = new Form(null, null, $builder);
        $fc->setAnonymousAttributesNames();
        return $fc;
    }

    /**
     * @return ValidatedValue|null
     */
    public function getSubmissionKey(): ?ValidatedValue
    {
        if($this->validator !== null && $this->formId !== null){
            return $this->validator->validateSubmissionKey($this->formId . "_submit__");
        }
        return null;
    }

    /**
     * @param FormSubmit|null $submit
     * @return void
     */
    private function setValidateAttrsIntoValidator(?FormSubmit $submit): void
    {
        if($submit !== null && $submit->_setValidateAttrs($this->validator)){
            return;
        }

        if($this->validate_attrs_hasBeenSet){
            if($this->validate_attrs_all){
                $this->validator->validate_all_except(...$this->validate_attrs);
            }else{
                $this->validator->validate_only(...$this->validate_attrs);
            }
        }

    }

}

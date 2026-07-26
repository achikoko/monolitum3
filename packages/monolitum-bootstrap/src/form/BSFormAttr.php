<?php

namespace monolitum\bootstrap\form;

use Closure;
use monolitum\bootstrap\datetime\BSFormControl_Datetime;
use monolitum\bootstrap\select\BSFormControl_Select;
use monolitum\bootstrap\style\BSColSpanResponsive;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\Div;
use monolitum\frontend\component\Span;
use monolitum\frontend\form\AbstractHtmlElementNodeFormAttr;
use monolitum\frontend\form\AttrExt_Form;
use monolitum\frontend\form\AttrExt_Form_DateTime;
use monolitum\frontend\form\AttrExt_Form_String;
use monolitum\frontend\form\FormControl;
use monolitum\frontend\form\FormControl_CheckBox;
use monolitum\frontend\form\FormControl_File;
use monolitum\frontend\form\FormControl_Number;
use monolitum\frontend\form\FormControl_Password;
use monolitum\frontend\form\FormControl_Select_Option;
use monolitum\frontend\form\FormControl_Select_OptionGroup;
use monolitum\frontend\form\FormControl_Text;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;
use monolitum\i18n\TSLang;
use monolitum\model\Attr;
use monolitum\model\Attr_Bool;
use monolitum\model\Attr_Date;
use monolitum\model\Attr_DateTime;
use monolitum\model\Attr_Decimal;
use monolitum\model\Attr_File;
use monolitum\model\Attr_Int;
use monolitum\model\Attr_String;
use monolitum\model\AttrExt_Validate;
use monolitum\model\AttrExt_Validate_Int;
use monolitum\model\AttrExt_Validate_String;
use monolitum\model\EntitiesManager;
use monolitum\model\Model;
use function monolitum\core\m;

class BSFormAttr extends AbstractHtmlElementNodeFormAttr
{

    private Renderable_Node|BSFormAttr $formWrapper;

    /**
     * @var string|HtmlElementNode|null
     */
    private string|HtmlElementNode|null $formText = null;

    /**
     * @var bool|null
     */
    private ?bool $labelRendersAfterControl = null;

    private ?BSColSpanResponsive $isRow = null;

    /**
     * If Closure: (BSFormAttr) -> FormControl
     * @var FormControl|Closure|null
     */
    private FormControl|Closure|null $customFormControl = null;

    /**
     * If Closure: (BSFormAttr, FormControl) -> void
     * @var Closure|null
     */
    private ?Closure $formControlUpdater = null;

    private array $inputGroupBefore = [];
    private array $inputGroupAfter = [];

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $formControlExtensions = [];
    private FormControl|null $formControl = null;
    private Renderable_Node|null $formControlActualRenderable = null;

    private ?Attr $attrRenderAs = null;

    public function __construct(Attr|string $attrId, ?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("div"), $attrId, $builder);
        $this->formWrapper = $this;
//        $this->experimental_letBuildChildsAfterBuild = true;
    }

    public function setFormText(string|HtmlElementNode|null $formText): self
    {
        $this->formText = $formText;
        return $this;
    }

    /**
     * Sets the length of the form input respect of the label. If the colspan is set to 12 at any point,
     * the label will become stacked on top.
     * @param BSColSpanResponsive $isRow
     * @return $this
     */
    public function setIsRow(BSColSpanResponsive $isRow): self
    {
        $this->isRow = $isRow;
        return $this;
    }

    /**
     * Sets a custom from control to this BSFormAttr. Additionally, a Closure that assigns default values after the form has been validated.
     * @param FormControl|Closure $customFormControl If Closure (BSFormAttr) -> FormControl
     * @param Closure|null $formControlUpdater (BSFormAttr, FormControl) -> void
     * @return $this
     */
    public function setCustomFormControl(
        FormControl|Closure $customFormControl,
        ?Closure $formControlUpdater = null
    ): self {
        $this->customFormControl = $customFormControl;
        $this->formControlUpdater = $formControlUpdater;
        return $this;
    }

    /**
     * @param string|Attr $attrRenderAs
     * @param class-string|Model|null $model
     */
    public function setAttrRenderAs(string|Attr $attrRenderAs, string|Model|null $model): void
    {
        if(!($attrRenderAs instanceof Attr)){
            if($model !== null){
                $attrRenderAs = EntitiesManager::findSelf()->getModel($model)->getAttr($attrRenderAs);
            }else{
                throw new DevPanic("Expected an Attr instance, not a string.");
            }
        }

        $this->attrRenderAs = $attrRenderAs;
    }

    /**
     * @return Attr|null
     */
    public function getAttrRenderAs(): ?Attr
    {
        return $this->attrRenderAs !== null ? $this->attrRenderAs : $this->getAttr();
    }

    public function prependInputGroup(string|TS|BSFormSubmit|null $inputGroupBefore): self
    {
        if(is_string($inputGroupBefore) || $inputGroupBefore instanceof TS){
            $this->inputGroupBefore[] = $inputGroupBefore;
        }else if($inputGroupBefore instanceof BSFormSubmit){
            $this->inputGroupBefore[] = $inputGroupBefore;
        }else{
            throw new DevPanic("Not accepted yet as input.");
        }

        return $this;
    }

    public function appendInputGroup(string|TS|BSFormSubmit|null $inputGroupAfter): self
    {
        if(is_string($inputGroupAfter) || $inputGroupAfter instanceof TS){
            $this->inputGroupAfter[] = $inputGroupAfter;
        }else if($inputGroupAfter instanceof BSFormSubmit){
            $this->inputGroupAfter[] = $inputGroupAfter;
        }else{
            throw new DevPanic("Not accepted yet as input.");
        }

        return $this;
    }

    public function onCheckForm(): void
    {
        parent::onCheckForm();

        $attr = $this->getAttrRenderAs();

        // TODO disable ENTER key using https://stackoverflow.com/questions/895171/prevent-users-from-submitting-a-form-by-hitting-enter

        if($this->hidden === true){
            $this->createFormControl();
            $this->formWrapper->append($this->formControlActualRenderable);
        }else{

            if($attr instanceof Attr_Bool){

                $this->formWrapper->addClass("form-check");

                $this->createFormControl();
                $this->formWrapper->append($this->formControlActualRenderable);

                if($this->getLabel() !== null){
                    $label = TS::render($this->getLabel(), TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage));
                    $this->formWrapper->append(
                        new BSFormLabel(function(BSFormLabel $it) use ($label) {
                            $it->setFor($this->getFullFieldName());
                            $it->append($label);
                        }, "form-check-label")
                    );
                }

                $this->labelRendersAfterControl = true;

            }else{

                // Not used in bootstrap 5.3
//                $this->formWrapper->addClass("form-group");

                /** @var ?HtmlElementNode $formLabel */
                $formLabel = null;
                if($this->getLabel() !== null){
                    $label = TS::render($this->getLabel(), TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage));
                    $formLabel = new BSFormLabel(function (BSFormLabel $it) use ($label) {
                        $it->setFor($this->getFullFieldName());
                        $it->append($label);
                    }, $this->isRow != null ? "col-form-label" : "form-label");
                }

                if($this->isRow != null){
                    $this->formWrapper->addClass("row");
                }

                $this->createFormControl();

                if($formLabel == null){
                    $this->formWrapper->append($this->formControlActualRenderable);
                }else if($this->isRow != null){
                    $this->isRow->buildInto($formLabel, true);

                    $formControlWrapper = new Div();

                    $formControlWrapper->append($this->formControlActualRenderable);
                    $this->isRow->buildInto($formControlWrapper);

                    if($this->labelRendersAfterControl){
                        $this->formWrapper->append($formControlWrapper);
                        $this->formWrapper->append($formLabel);
                    }else{
                        $this->formWrapper->append($formLabel);
                        $this->formWrapper->append($formControlWrapper);
                    }

                }else{

                    if($this->labelRendersAfterControl){
                        $this->formWrapper->append($this->formControlActualRenderable);
                        $this->formWrapper->append($formLabel);
                    }else{
                        $this->formWrapper->append($formLabel);
                        $this->formWrapper->append($this->formControlActualRenderable);
                    }

                }

            }

        }

    }

    public function onBeforeValidateForm(): void
    {
        parent::onBeforeValidateForm();

        if($this->formControlUpdater !== null){
            call_user_func($this->formControlUpdater, $this, $this->formControl);
        }

    }

    public function onNotValidateForm(): void
    {
        parent::onNotValidateForm();

        if($this->formControlUpdater !== null){
            call_user_func($this->formControlUpdater, $this, $this->formControl);
        }
    }

    public function onAfterValidateForm(): void
    {
        parent::onAfterValidateForm();

        if($this->hidden !== true){

            $invalidFeedback = null;
            if($this->isValid() === false){
                if($this->getInvalidText() !== null){
                    $invalidText = TS::renderAuto($this->getInvalidText(), $this->overwrittenLanguage);
                    $invalidFeedback = new Div(function (Div $it) use ($invalidText) {
                        $it->addClass("invalid-feedback");
                        $it->append($invalidText);
                    });
                }
                if($this->formControl !== null){
                    $this->formControl->addClass("is-invalid");
                }
            }else if($this->isValid() === true){
                if($this->formControl !== null){
                    $this->formControl->addClass("is-valid");
                }
            }

            $formText = null;
            if($this->formText !== null){
                if($this->formText instanceof HtmlElementNode){
                    $formText = $this->formText;
                    $formText->addClass("form-text");
                }else{
                    $formText = new Div(function (Div $it){
                        $it->addClass("form-text");
                        $it->append($this->formText);
                    });
                }

            }

            if($invalidFeedback){
                $this->formWrapper->append($invalidFeedback);
            }

            if($formText){
                $this->formWrapper->append($formText);
            }

        }
    }

    protected function createFormControl(): void
    {

        if($this->customFormControl !== null){
            if(is_callable($this->customFormControl)){
                $this->formControl = call_user_func($this->customFormControl, $this);
            }else{
                $this->formControl = $this->customFormControl;
            }
            $this->formControlActualRenderable = $this->formControl;
        }else {

            $attr = $this->getAttrRenderAs();
            $formExt = $this->getFormExt();
            $validateExt = $this->getValidateExt();

            $this->formControl = null;

            $finalLanguage = TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage); // TODO Active get finalLanguage

            if ($this->hasOverriddenEnum) {
                // If there is an enum, create a select
                $this->formControl = $this->createSelectFormControl($finalLanguage, $formExt, $validateExt);

            } else {

                if ($attr instanceof Attr_Bool) {

                    $this->formControl = new FormControl_CheckBox(function (FormControl_CheckBox $it) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());
                        if ($this->hidden === true)
                            $it->convertToHidden();

                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                            if ($it->hasValue())
                                $formControl->setValue($this->getValue());

                            if ($it->isDisabled())
                                $formControl->setDisabled();
                        };
                    });

                } else if ($attr instanceof Attr_String) {

                    if ($validateExt instanceof AttrExt_Validate_String && $validateExt->hasEnum()) {

                        $this->formControl = $this->createSelectFormControl($finalLanguage, $formExt, $validateExt);

                    } else if ($formExt instanceof AttrExt_Form_String && $formExt->isPassword()) {

                        $this->formControl = new FormControl_Password(function (FormControl_Password $it) {
                            $it->setId($this->getFullFieldName());
                            $it->setName($this->getFullFieldName());
                            $it->autocomplete(false);
                            if ($this->hidden === true)
                                $it->convertToHidden();

                            $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                                if ($it->hasValue())
                                    $formControl->setValue($this->getValue());

                                if ($it->isDisabled())
                                    $formControl->setDisabled();
                            };
                        });

                    } else {

                        $this->formControl = new FormControl_Text(function (FormControl_Text $it) use ($formExt, $finalLanguage) {
                            $it->setId($this->getFullFieldName());
                            $it->setName($this->getFullFieldName());
                            $it->autocomplete(false);

                            if ($formExt instanceof AttrExt_Form_String) {
                                $inputType = $formExt->getInputType();
                                if ($inputType !== null)
                                    $it->setInputType($inputType);
                            }
                            if ($this->getPlaceholder() != null)
                                $it->setPlaceholder(TS::unwrap($this->getPlaceholder(), $finalLanguage));

                            if ($this->hidden === true)
                                $it->convertToHidden();

                            $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                                if ($it->hasValue())
                                    $formControl->setValue($this->getValue());

                                if ($it->isDisabled())
                                    $formControl->setDisabled();
                            };

                        });

                    }

                } else if ($attr instanceof Attr_Int) {

                    $this->formControl = new FormControl_Number(function (FormControl_Number $it) use ($validateExt) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());

                        if ($validateExt instanceof AttrExt_Validate_Int) {
                            $it->min($validateExt->getMin());
                            $it->max($validateExt->getMax());
                        }

                        if ($this->hidden === true)
                            $it->convertToHidden();


                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                            if ($it->hasValue())
                                $formControl->setValue($this->getValue());

                            if ($it->isDisabled())
                                $formControl->setDisabled();
                        };
                    });

                } else if ($attr instanceof Attr_Decimal) {

                    $this->formControl = new FormControl_Number(function (FormControl_Number $it) use ($attr) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());
                        $decimals = $attr->getDecimals();

                        $it->step(1 / pow(10, $decimals));

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) use ($attr) {

                            if ($it->hasValue())
                                $formControl->setValue($attr->stringValue($this->getValue()));

                            if ($it->isDisabled())
                                $formControl->setDisabled();
                        };

                    });

                } else if ($attr instanceof Attr_Date) {

                    $this->formControl = new BSFormControl_Datetime(function (BSFormControl_Datetime $it) use ($formExt) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());
                        $it->setOnlyDate();

                        if ($formExt instanceof AttrExt_Form_DateTime && $formExt->getIsLongAway()) {
                            $it->setShowYearsFirst();
                        }

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                            if ($it->hasValue()) {
                                $datetime = $it->getValue();
                                if ($datetime !== null)
                                    $formControl->setValue(date_format($datetime, "Y-m-d"));
                            }

                            if ($it->isDisabled())
                                $formControl->setDisabled();
                        };

                    });

                } else if ($attr instanceof Attr_DateTime) {

                    $this->formControl = new BSFormControl_Datetime(function (BSFormControl_Datetime $it) use ($formExt) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());

                        if ($formExt instanceof AttrExt_Form_DateTime && $formExt->getIsLongAway()) {
                            $it->setShowYearsFirst();
                        }

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                            if ($it->hasValue()) {
                                $datetime = $it->getValue();
                                if ($datetime !== null)
                                    $formControl->setValue(date_format($datetime, "Y-m-d H:i:s"));
                            }

                            if ($it->isDisabled())
                                $formControl->setDisabled();
                        };

                    });

                } else if ($attr instanceof Attr_File) {

                    $this->formControl = new FormControl_File(function (FormControl_File $it) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {

                            if ($it->isDisabled())
                                $formControl->setDisabled();

                        };

                    });

                }
            }

            if (count($this->inputGroupBefore) > 0 || count($this->inputGroupAfter) > 0) {
                $this->formControlActualRenderable = new Div(function (Div $it) {
                    $it->addClass("input-group");

                    foreach ($this->inputGroupBefore as $input) {
                        if (is_string($input) || $input instanceof TS) {
                            $it->append(new Span(function (Span $it) use ($input) {
                                $it->addClass("input-group-text");
                                $it->append($input);
                            }));
                        } else {
                            $it->append($input);
                        }
                    }

                    $it->append($this->formControl);

                    foreach ($this->inputGroupAfter as $input) {
                        if (is_string($input) || $input instanceof TS) {
                            $it->append(new Span(function (Span $it) use ($input) {
                                $it->addClass("input-group-text");
                                $it->append($input);
                            }));
                        } else {
                            $it->append($input);
                        }
                    }

                });
            } else {
                $this->formControlActualRenderable = $this->formControl;
            }
        }

        foreach ($this->formControlExtensions as $formControlExtension){
            $formControlExtension->_setElementComponent($this->formControl);
            $formControlExtension->apply();
        }

    }

    public function render(): Renderable|array|null
    {
        if($this->hidden === true){
            return parent::renderChildren();
        }else{
            return parent::render();
        }
    }

    /**
     * @param string|null $finalLanguage
     * @param AttrExt_Form|null $formExt
     * @param AttrExt_Validate|null $validateExt
     * @return BSFormControl_Select
     */
    public function createSelectFormControl(?string $finalLanguage, ?AttrExt_Form $formExt, AttrExt_Validate|null $validateExt): BSFormControl_Select
    {
        return new BSFormControl_Select(function (BSFormControl_Select $it) use ($finalLanguage, $formExt, $validateExt) {
            $it->setId($this->getFullFieldName());
            $it->setName($this->getFullFieldName());

            if ($this->hidden === true)
                $it->convertToHidden();

            if($formExt !== null) {
                $nullLabel = $formExt->getNullLabel();
                if ($formExt instanceof AttrExt_Form_String) {
                    $it->setSearchable($formExt->isSearchable());
                }
            }else{
                $nullLabel = null;
            }

            if ($validateExt == null || $validateExt->isNullable()) {

                M(new FormControl_Select_Option(
                    "",
                    function (FormControl_Select_Option $it) use ($finalLanguage, $nullLabel) {

                        $it->append($nullLabel !== null ? TS::render($nullLabel, $finalLanguage) : "");

//                        if ($selected === null)
//                            $it->setSelected();

                    }));

            } else {

                $it->setAttribute("data-placeholder", TS::unwrap($nullLabel, $finalLanguage));

                // TODO there is a weird bug when field is not nullable but $nullLabel is null, an empty option appears
                // Look at the registration form, field nif_type
                M(new FormControl_Select_Option(
                    "",
                    function (FormControl_Select_Option $it) {
                        $it->append("");
                    })
                );

            }

            $enumeration = null;

            if ($this->hasOverriddenEnum) {
                $enumeration = $this->overriddenEnum;
            }else if($validateExt instanceof AttrExt_Validate_String) {
                $enumeration = $validateExt->getEnums();
            }

            if($enumeration !== null){

                $currentGroup = null;
                $currentGroupElement = null;

                foreach ($enumeration as $itemKey => $itemLabel) {
                    $content = TS::render($itemLabel, $finalLanguage);

                    $itemGroup = $enumeration->getGroupOfKey($itemKey);

                    if($itemGroup === null){

                        if($currentGroupElement !== null){
                            M($currentGroupElement);
                            $currentGroup = null;
                            $currentGroupElement = null;
                        }

                        M(new FormControl_Select_Option($itemKey, function (FormControl_Select_Option $it) use ($content, $itemKey) {
                            $it->append($content);
//                            if ($itemKey == $selected)
//                                $it->setSelected();
                        }));

                    }else{
                        if($currentGroup !== $itemGroup){
                            if($currentGroupElement !== null){
                                M($currentGroupElement);
                                $currentGroup = null;
                                $currentGroupElement = null;
                            }

                            $currentGroupElement = new FormControl_Select_OptionGroup($itemGroup->getLabel());
                            $currentGroup = $itemGroup;
                        }

                        $currentGroupElement->receive(new FormControl_Select_Option($itemKey, function (FormControl_Select_Option $it) use ($content, $itemKey) {
                            $it->append($content);
//                            if ($itemKey == $selected)
//                                $it->setSelected();
                        }));
                    }

                }

                if($currentGroupElement !== null){
                    M($currentGroupElement);
                }

                $this->formControlUpdater = function (BSFormAttr $it, FormControl $formControl) {
                    // TODO create a Map with keys and elements, to search in O(1)

                    $selected = null;
                    if ($this->hasValue())
                        $selected = $this->getValue();
                    $formControl->setValue($selected);

                    if ($this->isDisabled())
                        $formControl->setDisabled();

                    foreach ($formControl->getChildren() as $child) {
                        if ($child instanceof FormControl_Select_Option) {
                            $value = $child->getValue();
                            if (empty($value) && $selected == null || $value == $selected)
                                $child->setSelected();

                        }else if($child instanceof FormControl_Select_OptionGroup){

                            foreach ($child->getChildren() as $child2) {
                                if ($child2 instanceof FormControl_Select_Option) {
                                    $value = $child2->getValue();
                                    if (empty($value) && $selected == null || $value == $selected)
                                        $child2->setSelected();
                                }
                            }

                        }
                    }

                };

            }

        });
    }

    public function addExtensionToFormControl(HtmlElementNodeExtension $extension): self
    {
        $this->formControlExtensions[] = $extension;
        return $this;
    }

}

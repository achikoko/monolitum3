<?php

namespace monolitum\bootstrap\form;

use Closure;
use monolitum\bootstrap\select\BSFormControl_Select;
use monolitum\bootstrap\style\BSColSpanResponsive;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\Div;
use monolitum\frontend\component\Span;
use monolitum\frontend\form\AbstractHtmlElementNodeFormAttr;
use monolitum\frontend\form\AttrExt_Form;
use monolitum\frontend\form\AttrExt_Form_String;
use monolitum\frontend\form\FormControl;
use monolitum\frontend\form\FormControl_CheckBox;
use monolitum\frontend\form\FormControl_Date;
use monolitum\frontend\form\FormControl_DateTime;
use monolitum\frontend\form\FormControl_File;
use monolitum\frontend\form\FormControl_Number;
use monolitum\frontend\form\FormControl_Password;
use monolitum\frontend\form\FormControl_Select_Option;
use monolitum\frontend\form\FormControl_Select_OptionGroup;
use monolitum\frontend\form\FormControl_Text;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;
use monolitum\i18n\TSLang;
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_Bool;
use monolitum\model\attr\Attr_Date;
use monolitum\model\attr\Attr_DateTime;
use monolitum\model\attr\Attr_Decimal;
use monolitum\model\attr\Attr_File;
use monolitum\model\attr\Attr_Int;
use monolitum\model\attr\Attr_String;
use monolitum\model\AttrExt_Validate;
use monolitum\model\AttrExt_Validate_Int;
use monolitum\model\AttrExt_Validate_String;
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

    private FormControl|Closure|null $customFormControl = null;

    private array $inputGroupBefore = [];
    private array $inputGroupAfter = [];

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
     * If Closure (BSFormAttr) -> FormControl
     * @param FormControl|Closure $customFormControl
     * @return $this
     */
    public function setCustomFormControl(FormControl|Closure $customFormControl): self
    {
        $this->customFormControl = $customFormControl;
        return $this;
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

    public function onAfterBuildForm(): void
    {
        $attr = $this->getAttr();
//        $ext = $this->getFormExt();

        // TODO disable ENTER key using https://stackoverflow.com/questions/895171/prevent-users-from-submitting-a-form-by-hitting-enter

        if($this->hidden === true){
            $this->formWrapper->append($this->createFormControl());
        }else{

            $invalidFeedback = null;
            if($this->isValid() === false){
                $invalidText = TS::unwrapAuto($this->getInvalidText(), $this->overwrittenLanguage);
                if($invalidText !== null){
                    $invalidFeedback = new Div(function (Div $it) use ($invalidText) {
                        $it->addClass("invalid-feedback");
                        $it->append($invalidText);
                    });
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

            if($attr instanceof Attr_Bool){

                $this->formWrapper->addClass("form-check");

                $this->formWrapper->append($this->createFormControl());

                $label = TS::unwrap($this->getLabel(), TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage));
                if(is_string($label) && strlen($label) > 0){
                    $this->formWrapper->append(
                        new BSFormLabel(function(BSFormLabel $it) use ($label) {
                            $it->setFor($this->getFullFieldName());
                            $it->setContent($label);
                        }, "form-check-label")
                    );
                }

                if($invalidFeedback){
                    $this->formWrapper->append($invalidFeedback);
                }

                if($formText){
                    $this->formWrapper->append($formText);
                }

                $this->labelRendersAfterControl = true;

            }else{

                // Not used in bootstrap 5.3
//                $this->formWrapper->addClass("form-group");

                /** @var ?HtmlElementNode $formLabel */
                $formLabel = null;
                $label = TS::unwrap($this->getLabel(), TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage));
                if(is_string($label) && strlen($label) > 0){
                    $formLabel = new BSFormLabel(function (BSFormLabel $it) use ($label) {
                        $it->setFor($this->getFullFieldName());
                        $it->setContent($label);
                    }, $this->isRow != null ? "col-form-label" : "form-label");
                }

                if($this->isRow != null){
                    $this->formWrapper->addClass("row");
                }

                $formControl = $this->createFormControl();

                if($formLabel == null){
                    $this->formWrapper->append($formControl);
                }else if($this->isRow != null){
                    $this->isRow->buildInto($formLabel, true);

                    $formControlWrapper = new Div();

                    $formControlWrapper->append($formControl);
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
                        $this->formWrapper->append($formControl);
                        $this->formWrapper->append($formLabel);
                    }else{
                        $this->formWrapper->append($formLabel);
                        $this->formWrapper->append($formControl);
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

    }

    protected function createFormControl(): mixed
    {

        if($this->customFormControl !== null){
            if(is_callable($this->customFormControl)){
                return call_user_func($this->customFormControl, $this);
            }else{
                return $this->customFormControl;
            }
        }

        $attr = $this->getAttr();
        $formExt = $this->getFormExt();
        $validateExt = $this->getValidateExt();
        $isValid = $this->isValid();

        $formControl = null;

        $finalLanguage = TSLang::pushAndGetLangWithOverwritten($this->overwrittenLanguage); // TODO Active get finalLanguage

        if($this->hasOverriddenEnum)
        {
            // If there is an enum, create a select
            $formControl = $this->createSelectFormControl($isValid, $finalLanguage, $formExt, $validateExt);

        }else {

            if ($attr instanceof Attr_Bool) {

                $formControl = new FormControl_CheckBox(function (FormControl_CheckBox $it) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if ($this->hasValue())
                        $it->setValue($this->getValue());

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            } else if ($attr instanceof Attr_String) {

                if ($validateExt instanceof AttrExt_Validate_String && $validateExt->hasEnum()) {

                    $formControl = $this->createSelectFormControl($isValid, $finalLanguage, $formExt, $validateExt);

//            }else if($formExt instanceof AttrExt_Form_String && $formExt->isHtml()){
//
////                $formControl = new EditorJS(function (EditorJS $it) use ($ext) {
////                    $it->setId($this->getName());
////                    $it->setName($this->getName());
////
////                    if($this->hasValue())
////                        $it->setValue($this->getValue());
////
////                    $it->style()->height(CSSSize::px(150));
////
////                });
//
//                $formControl = new FormControl_TextArea_Html(function (FormControl_TextArea_Html $it) use ($formExt) {
//                    $it->setId($this->getFullFieldName());
//                    $it->setName($this->getFullFieldName());
//                    $it->autocomplete(false);
//
//                    if($this->hidden === true)
//                        $it->convertToHidden();
//
//                    if($this->hasValue())
//                        $it->setValue($this->getValue());
//
//                });

                } else if ($formExt instanceof AttrExt_Form_String && $formExt->isPassword()) {

                    $formControl = new FormControl_Password(function (FormControl_Password $it) use ($isValid) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());
                        $it->autocomplete(false);
                        if ($this->hasValue())
                            $it->setValue($this->getValue());
                        if ($isValid !== null)
                            $it->addClass($isValid ? "is-valid" : "is-invalid");

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                            $it->setDisabled();

                        // TODO ask form for default value
                    });

                } else {

                    $formControl = new FormControl_Text(function (FormControl_Text $it) use ($formExt, $finalLanguage, $isValid) {
                        $it->setId($this->getFullFieldName());
                        $it->setName($this->getFullFieldName());
                        $it->autocomplete(false);

                        if ($formExt instanceof AttrExt_Form_String) {
                            $inputType = $formExt->getInputType();
                            if ($inputType !== null)
                                $it->setInputType($inputType);
                        }

                        if ($this->hasValue())
                            $it->setValue($this->getValue());
                        if ($isValid !== null)
                            $it->addClass($isValid ? "is-valid" : "is-invalid");

                        if ($this->getPlaceholder() != null)
                            $it->setPlaceholder(TS::unwrap($this->getPlaceholder(), $finalLanguage));

                        if ($this->hidden === true)
                            $it->convertToHidden();

                        if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                            $it->setDisabled(true);

                    });

                }

            } else if ($attr instanceof Attr_Int) {

                $formControl = new FormControl_Number(function (FormControl_Number $it) use ($validateExt, $isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if ($this->hasValue()) {
                        $it->setValue($this->getValue());
                    }

                    if ($validateExt instanceof AttrExt_Validate_Int) {
                        $it->min($validateExt->getMin());
                        $it->max($validateExt->getMax());
                    }

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            } else if ($attr instanceof Attr_Decimal) {

                $formControl = new FormControl_Number(function (FormControl_Number $it) use ($attr, $isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    $decimals = $attr->getDecimals();

                    $it->step(1 / pow(10, $decimals));

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($this->hasValue()) {
                        $it->setValue($this->getValue() / pow(10, $decimals));
                    }
                    if ($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            } else if ($attr instanceof Attr_Date) {

                $formControl = new FormControl_Date(function (FormControl_Date $it) use ($isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if ($this->hasValue()) {
                        $datetime = $this->getValue();
                        if ($datetime !== null)
                            $it->setValue(date_format($datetime, "Y-m-d"));
                    }
                    if ($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            } else if ($attr instanceof Attr_DateTime) {

                $formControl = new FormControl_DateTime(function (FormControl_DateTime $it) use ($isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if ($this->hasValue()) {
                        $datetime = $this->getValue();
                        if ($datetime !== null)
                            $it->setValue(date_format($datetime, "Y-m-d H:i:s"));
                    }
                    if ($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            } else if ($attr instanceof Attr_File) {

                $formControl = new FormControl_File(function (FormControl_File $it) use ($isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());

                    if ($this->hidden === true)
                        $it->convertToHidden();

                    if ($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            }
        }

        if(count($this->inputGroupBefore) > 0 || count($this->inputGroupAfter) > 0){
            return new Div(function (Div $it) use ($formControl) {
                $it->addClass("input-group");

                foreach ($this->inputGroupBefore as $input){
                    if(is_string($input) || $input instanceof TS){
                        $it->append(new Span(function (Span $it) use ($input){
                            $it->addClass("input-group-text");
                            $it->append($input);
                        }));
                    }else{
                        $it->append($input);
                    }
                }

                $it->append($formControl);

                foreach ($this->inputGroupAfter as $input){
                    if(is_string($input) || $input instanceof TS){
                        $it->append(new Span(function (Span $it) use ($input){
                            $it->addClass("input-group-text");
                            $it->append($input);
                        }));
                    }else{
                        $it->append($input);
                    }
                }

            });
        }

        return $formControl;

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
     * @param bool|null $isValid
     * @param string|null $finalLanguage
     * @param AttrExt_Form|null $formExt
     * @param AttrExt_Validate|null $validateExt
     * @return BSFormControl_Select
     */
    public function createSelectFormControl(?bool $isValid, ?string $finalLanguage, ?AttrExt_Form $formExt, AttrExt_Validate|null $validateExt): BSFormControl_Select
    {
        return new BSFormControl_Select(function (BSFormControl_Select $it) use ($isValid, $finalLanguage, $formExt, $validateExt) {
            $it->setId($this->getFullFieldName());
            $it->setName($this->getFullFieldName());

            $selected = null;
            if ($this->hasValue())
                $selected = $this->getValue();
            $it->setValue($selected);

            if ($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                $it->setDisabled();

            if ($isValid !== null)
                $it->addClass($isValid ? "is-valid" : "is-invalid");

            if ($this->hidden === true)
                $it->convertToHidden();

            $nullLabel = $formExt->getNullLabel();;
            if ($formExt instanceof AttrExt_Form_String) {
                $it->setSearchable($formExt->isSearchable());
            }

            if ($validateExt == null || $validateExt->isNullable()) {

                M(new FormControl_Select_Option(
                    "",
                    $nullLabel !== null ? TS::unwrap($nullLabel, $finalLanguage) : "",
                    function (FormControl_Select_Option $it) use ($finalLanguage, $selected, $nullLabel) {

                        if ($selected === null)
                            $it->setSelected();

                    }));

            } else {

                $it->setAttribute("data-placeholder", TS::unwrap($nullLabel, $finalLanguage));

                M(new FormControl_Select_Option(
                    "",
                    "",
                    function (FormControl_Select_Option $it) use ($selected) {
                        $it->setContent("");
                    }));

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
                    $content = TS::unwrap($itemLabel, $finalLanguage);

                    $itemGroup = $enumeration->getGroupOfKey($itemKey);

                    if($itemGroup === null){

                        if($currentGroupElement !== null){
                            M($currentGroupElement);
                            $currentGroup = null;
                            $currentGroupElement = null;
                        }

                        M(new FormControl_Select_Option($itemKey, $content, function (FormControl_Select_Option $it) use ($itemKey, $selected) {
                            if ($itemKey == $selected)
                                $it->setSelected();
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

                        $currentGroupElement->receive(new FormControl_Select_Option($itemKey, $content, function (FormControl_Select_Option $it) use ($itemKey, $selected) {
                            if ($itemKey == $selected)
                                $it->setSelected();
                        }));
                    }

                }

                if($currentGroupElement !== null){
                    M($currentGroupElement);
                }

            }

        });
    }

}

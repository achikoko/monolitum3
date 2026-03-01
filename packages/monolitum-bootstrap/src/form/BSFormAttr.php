<?php

namespace monolitum\bootstrap\form;

use Closure;
use monolitum\bootstrap\style\BSColSpanResponsive;
use monolitum\frontend\component\Div;
use monolitum\frontend\form\AbstractHtmlElementNodeFormAttr;
use monolitum\frontend\form\AttrExt_Form_String;
use monolitum\frontend\form\FormControl_CheckBox;
use monolitum\frontend\form\FormControl_Date;
use monolitum\frontend\form\FormControl_DateTime;
use monolitum\frontend\form\FormControl_File;
use monolitum\frontend\form\FormControl_Number;
use monolitum\frontend\form\FormControl_Password;
use monolitum\frontend\form\FormControl_Select;
use monolitum\frontend\form\FormControl_Select_Option;
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

    public function setIsRow(BSColSpanResponsive $isRow): self
    {
        $this->isRow = $isRow;
        return $this;
    }

    public function onAfterBuildForm(): void
    {
        $attr = $this->getAttr();
//        $ext = $this->getFormExt();

        if($this->hidden === true){
            $this->formWrapper->append($this->createFormControl());
        }else{

            $invalidFeedback = null;
            if($this->isValid() === false){
                $invalidText = TS::unwrap($this->getInvalidText(), TSLang::findWithOverwritten($this->overwrittenLanguage));
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

                $label = TS::unwrap($this->getLabel(), TSLang::findWithOverwritten($this->overwrittenLanguage));
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

                $this->formWrapper->addClass("form-group");

                /** @var ?HtmlElementNode $formLabel */
                $formLabel = null;
                $label = TS::unwrap($this->getLabel(), TSLang::findWithOverwritten($this->overwrittenLanguage));
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

        $attr = $this->getAttr();
        $formExt = $this->getFormExt();
        $validateExt = $this->getValidateExt();
        $isValid = $this->isValid();

        $formControl = null;

        $finalLanguage = TSLang::findWithOverwritten($this->overwrittenLanguage); // TODO Active get finalLanguage

        if($attr instanceof Attr_Bool){

            $formControl = new FormControl_CheckBox(function(FormControl_CheckBox $it){
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue())
                    $it->setValue($this->getValue());

                if($this->hidden === true)
                    $it->convertToHidden();

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

            });

        } else if($attr instanceof Attr_String){

            if($validateExt instanceof AttrExt_Validate_String && $validateExt->hasEnum() || $this->hasOverriddenEnum){

                $formControl = new FormControl_Select(function (FormControl_Select $it) use ($isValid, $finalLanguage, $formExt, $validateExt) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());

                    $selected = null;
                    if($this->hasValue())
                        $selected = $this->getValue();
                    $it->setValue($selected);

                    if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled();

                    if($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if($this->hidden === true)
                        $it->convertToHidden();

                    $it->setPicker();

                    $nullLabel = null;
                    if($formExt instanceof AttrExt_Form_String){
                        $nullLabel = $formExt->getNullLabel();

                        $it->setSearchable($formExt->isSearchable());
                    }

                    if($validateExt == null || $validateExt->isNullable()){

                        M(new FormControl_Select_Option(
                            "",
                            $nullLabel !== null ? TS::unwrap($nullLabel, $finalLanguage) : "",
                            function (FormControl_Select_Option $it) use ($finalLanguage, $selected, $nullLabel) {

                            if($selected === null)
                                $it->setSelected();

                        }));

                    }else{

                        $it->setAttribute("data-placeholder", TS::unwrap($nullLabel, $finalLanguage));

                        M(new FormControl_Select_Option(
                            "",
                            "",
                            function (FormControl_Select_Option $it) use ($selected) {
                            $it->setContent("");
                        }));

                    }

                    if($this->hasOverriddenEnum){
                        foreach ($this->overriddenEnum as $itemKey => $itemValue) {

                            if (is_string($itemKey)) {
                                $item = $itemKey;
                                $content = $itemValue;
                            } else if (is_array($itemValue)) {
                                $item = $itemValue[0];
                                $content = $itemValue[1];
                            }else{
                                $item = $itemValue;
                                $content = $itemValue;
                            }

                            if(is_string($content)){
                                $content = TS::unwrap($content, $finalLanguage);
                            }

                            M(new FormControl_Select_Option($item, $content, function (FormControl_Select_Option $it) use ($item, $selected) {
                                if ($item == $selected)
                                    $it->setSelected();
                            }));
                        }
                    }else{
                        foreach ($validateExt->getEnums() as $itemKey => $itemValue) {
                            $item = null;
                            if (is_string($itemKey)) {
                                $item = $itemKey;
                            } else if (is_array($itemValue)) {
                                $item = $itemValue[0];
                            }

                            M(new FormControl_Select_Option($item, TS::unwrap($validateExt->getEnumString($item), $finalLanguage),
                                function (FormControl_Select_Option $it) use ($item, $selected) {

                                if ($item == $selected)
                                    $it->setSelected();

                            }));
                        }
                    }


                });

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

            }else if($formExt instanceof AttrExt_Form_String && $formExt->isPassword()){

                $formControl = new FormControl_Password(function(FormControl_Password $it) use ($isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    $it->autocomplete(false);
                    if($this->hasValue())
                        $it->setValue($this->getValue());
                    if($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if($this->hidden === true)
                        $it->convertToHidden();

                    if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled();

                    // TODO ask form for default value
                });

            }else{

                $formControl = new FormControl_Text(function(FormControl_Text $it) use ($formExt, $finalLanguage, $isValid) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    $it->autocomplete(false);

                    if($formExt instanceof AttrExt_Form_String){
                        $inputType = $formExt->getInputType();
                        if($inputType !== null)
                            $it->setInputType($inputType);
                    }

                    if($this->hasValue())
                        $it->setValue($this->getValue());
                    if($isValid !== null)
                        $it->addClass($isValid ? "is-valid" : "is-invalid");

                    if($this->getPlaceholder() != null)
                        $it->setPlaceholder(TS::unwrap($this->getPlaceholder(), $finalLanguage));

                    if($this->hidden === true)
                        $it->convertToHidden();

                    if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                        $it->setDisabled(true);

                });

            }

        }else if($attr instanceof Attr_Int){

            $formControl = new FormControl_Number(function(FormControl_Number $it) use ($validateExt, $isValid) {
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue()){
                    $it->setValue($this->getValue());
                }

                if($validateExt instanceof AttrExt_Validate_Int){
                    $it->min($validateExt->getMin());
                    $it->max($validateExt->getMax());
                }

                if($this->hidden === true)
                    $it->convertToHidden();

                if($isValid !== null)
                    $it->addClass($isValid ? "is-valid" : "is-invalid");

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

            });

        }else if($attr instanceof Attr_Decimal){

            $formControl = new FormControl_Number(function(FormControl_Number $it) use ($attr, $isValid) {
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                $decimals = $attr->getDecimals();

                $it->step(1 / pow(10, $decimals));

                if($this->hidden === true)
                    $it->convertToHidden();

                if($this->hasValue()){
                    $it->setValue($this->getValue() / pow(10, $decimals));
                }
                if($isValid !== null)
                    $it->addClass($isValid ? "is-valid" : "is-invalid");

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

            });

        }else if($attr instanceof Attr_Date){

            $formControl = new FormControl_Date(function(FormControl_Date $it) use ($isValid) {
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue()){
                    $datetime = $this->getValue();
                    if($datetime !== null)
                        $it->setValue(date_format($datetime, "Y-m-d"));
                }
                if($isValid !== null)
                    $it->addClass($isValid ? "is-valid" : "is-invalid");

                if($this->hidden === true)
                    $it->convertToHidden();

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

            });

        }else if($attr instanceof Attr_DateTime){

            $formControl = new FormControl_DateTime(function(FormControl_DateTime $it) use ($isValid) {
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue()){
                    $datetime = $this->getValue();
                    if($datetime !== null)
                        $it->setValue(date_format($datetime, "Y-m-d H:i:s"));
                }
                if($isValid !== null)
                    $it->addClass($isValid ? "is-valid" : "is-invalid");

                if($this->hidden === true)
                    $it->convertToHidden();

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

            });

        }else if($attr instanceof Attr_File){

            $formControl = new FormControl_File(function(FormControl_File $it) use ($isValid) {
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());

                if($this->hidden === true)
                    $it->convertToHidden();

                if($isValid !== null)
                    $it->addClass($isValid ? "is-valid" : "is-invalid");

                if($this->disabled !== null ? $this->disabled : $this->form->isDisabled())
                    $it->setDisabled(true);

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

}

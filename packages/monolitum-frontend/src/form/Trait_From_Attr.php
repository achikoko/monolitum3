<?php

namespace monolitum\frontend\form;

use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;
use monolitum\model\attr\Attr;
use monolitum\model\AttrExt_Validate;
use monolitum\model\enum\Enumeration;

trait Trait_From_Attr
{

    protected Form $form;

    protected string|Attr $attr;

    protected ?AttrExt_Form $formExt = null;

    protected ?AttrExt_Validate $validateExt = null;

    protected TS|string|null $label = null;

    protected mixed $overwrittenLanguage = null;

    protected TS|string|null $placeholder = null;

    protected ?bool $disabled = null;

    protected ?bool $hidden = null;

    /// ////////////////////
    /// Overridden Invalid TEXT
    /// ////////////////////

    protected bool $userSetInvalid = false;

    protected TS|string|HtmlElementNode|null $overwrittenInvalidText = null;

    /// ////////////////////
    /// Overridden VALUE
    /// ////////////////////

    protected bool $hasOverriddenValue = false;

    protected mixed $overriddenValue;

    /// ////////////////////
    /// Overridden ENUM
    /// ////////////////////

    protected bool $hasOverriddenEnum = false;

    protected ?Enumeration $overriddenEnum;

    public function disabled(bool $disabled=true): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @param bool|null $hidden
     */
    public function hidden(?bool $hidden=true): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @param string|HtmlElementNode|TS|null $string
     * @return $this
     */
    public function setOverrideInvalid(HtmlElementNode|string|TS $string=null): void
    {
        $this->userSetInvalid = true;
        $this->overwrittenInvalidText = $string;
    }

    /**
     * @param mixed $value
     */
    public function setOverrideValue(mixed $value): void
    {
        $this->hasOverriddenValue = true;
        $this->overriddenValue = $value;
    }

    /**
     * Needs to be an associative array: (string $key) -> (string|TS $text)
     * @param string[]|TS[] $enum
     */
    public function setOverrideEnum(array|Enumeration $enum): void
    {
        $this->hasOverriddenEnum = true;
        $this->overriddenEnum = Enumeration::wrap($enum);
    }

    /**
     * @param string|TS $label
     */
    public function label(string|TS $label): void
    {
        $this->label = $label;
    }

    /**
     * @param mixed $language
     */
    public function language(mixed $language): void
    {
        $this->overwrittenLanguage = $language;
    }

    /**
     * @param string|TS $placeholder
     */
    public function placeholder(string|TS $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    protected function getFullFieldName(): string
    {
        return $this->form->_getFullFieldName($this->attr);
    }

    protected function getLabel(): string|TS|null
    {
        $label = null;
        if($label == null)
            $label = $this->label;
        if($label == null && $this->formExt != null)
            $label = $this->formExt->getLabel();
        return $label;
    }

    public function getPlaceholder(): string|TS|null
    {
        return $this->placeholder;
    }

    /**
     * Returns if the value that user set is invalid.
     * @return bool|null
     */
    protected function isValid(): ?bool
    {
        if($this->form->isSilentValidation())
            return null;
        $isValid = $this->form->getValidatedValue($this->attr);
        if($isValid === null)
            return null;
        return $isValid->isValid() && !$this->userSetInvalid;
    }

    /**
     * Returns if the value that user set is invalid.
     * @return string|TS|HtmlElementNode|null
     */
    protected function getInvalidText(): HtmlElementNode|string|TS|null
    {
        if($this->form->isSilentValidation())
            return null;
        $isValid = $this->form->getValidatedValue($this->attr);
        if($isValid === null || ($isValid->isValid() && !$this->userSetInvalid))
            return null;

        $error = $this->overwrittenInvalidText;
        if($error == null)
            $error = $isValid->getError();

        return $error;
    }

    /**
     * Tells if there is a value ready to be displayed to the user.
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->hasOverriddenValue ? true : $this->form->getDisplayValue($this->attr)->isWellFormat();
    }

    public function getValue(): mixed
    {
        return $this->hasOverriddenValue ? $this->overriddenValue : $this->form->getDisplayValue($this->attr)->getValue();
    }

    public function getAttr(): Attr|string
    {
        return $this->attr;
    }

    public function getFormExt(): ?AttrExt_Form
    {
        return $this->formExt;
    }

    public function getValidateExt(): ?AttrExt_Validate
    {
        return $this->validateExt;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

}

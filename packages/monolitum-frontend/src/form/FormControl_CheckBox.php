<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;

class FormControl_CheckBox extends FormControl
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder, "form-check-input");
        $this->getElement()->setAttribute("type", "checkbox");
    }

    public function setValue(?string $value): void
    {
        $element = $this->getElement();
        if($value)
            $element->setAttribute("checked", "");
    }

}


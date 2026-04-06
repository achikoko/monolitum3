<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\i18n\TS;

class FormControl_Text extends FormControl
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "text");
    }

    public function setInputType(string $inputType): void
    {
        $this->getElement()->setAttribute("type", $inputType);
    }

    public function setPlaceholder(TS|string $placeholder): void
    {
        $element = $this->getElement();
        $element->setAttribute("placeholder", TS::unwrap($placeholder));
    }

}


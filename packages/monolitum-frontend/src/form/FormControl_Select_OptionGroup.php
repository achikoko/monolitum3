<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class FormControl_Select_OptionGroup extends HtmlElementNode
{

    public function __construct(string $label=null, ?Closure $builder = null,)
    {
        parent::__construct(new HtmlElement("optgroup"), $builder);
        $option = $this->getElement();
        $option->setAttribute("label", $label);
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $option = $this->getElement();
        $option->setAttribute("label", $value);
    }

}


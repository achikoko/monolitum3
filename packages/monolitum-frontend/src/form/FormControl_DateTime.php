<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;

class FormControl_DateTime extends FormControl
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "datetime-local");
        // https://weareoutman.github.io/clockpicker/
    }

}


<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\core\GlobalContext;
use monolitum\frontend\html\HtmlElement;

class FormControl_Hidden extends FormControl
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "hidden");
    }

}


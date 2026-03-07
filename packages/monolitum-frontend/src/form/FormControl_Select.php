<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

class FormControl_Select extends FormControl
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("select"), $builder, "form-select");
    }

    public function render(): Renderable|array|null
    {
        // No children are rendered if it is hidden
        if($this->getElement()->getAttribute("type") !== "hidden"){
            Renderable_Node::renderRenderedTo($this->renderChildren(), $this->getElement());
        }
        return Rendered::of($this->getElement());
    }

}


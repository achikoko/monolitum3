<?php
namespace monolitum\bootstrap\form;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class BSFormLabel extends HtmlElementNode
{

    public function __construct(callable $builder = null, private readonly string $class = "form-label")
    {
        parent::__construct(new HtmlElement("label"), $builder);
        $this->getElement()->addClass($this->class);
        $this->getElement()->setRequireEndTag();

    }

    public function setFor(string $name): self
    {
        $this->getElement()->setAttribute("for", $name);
        return $this;
    }

}


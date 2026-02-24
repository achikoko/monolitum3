<?php
namespace monolitum\bootstrap\form;

use Closure;
use monolitum\bootstrap\values\BSColor;
use monolitum\frontend\form\FormSubmit;
use monolitum\frontend\html\HtmlElement;

class BSFormSubmit extends FormSubmit
{

    private ?BSColor $color = null;
    private bool $outline = false;

    public function __construct(?Closure $builder)
    {
        parent::__construct(new HtmlElement("button"), $builder);
        $this->getElement()->setAttribute("type", "submit");
        $this->getElement()->addClass("btn");
        $this->getElement()->setRequireEndTag();
    }

    public function color(BSColor $color, bool $outline = false): self
    {
        $this->color = $color;
        $this->outline = $outline;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        parent::onAfterBuild();
        if($this->color != null){
            if($this->outline)
                $this->getElement()->addClass("btn-outline-" . $this->color->getValue());
            else
                $this->getElement()->addClass("btn-" . $this->color->getValue());
        }else{
            $this->getElement()->addClass("btn-primary");
        }
    }

    public function onAfterBuildForm(): void
    {

        $name = $this->getFinalName();
        if(!empty($name)){
            $this->setAttribute("name", $name);
        }

        // Method is the same as parent
//        $method = $this->getFinalCustomFormMethod();
//        if($method !== null){
            //$this->setAttribute("formmethod", $method);
//        }

        $linkResolver = $this->getFinalCustomLinkResolver();
        if($linkResolver !== null){
            $this->setAttribute("formaction", $linkResolver->resolve(), false);
        }

    }

}


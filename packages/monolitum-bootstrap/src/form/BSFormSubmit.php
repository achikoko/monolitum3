<?php
namespace monolitum\bootstrap\form;

use Closure;
use monolitum\bootstrap\component\TraitBSButton;
use monolitum\bootstrap\values\BSColor;
use monolitum\frontend\form\FormSubmit;
use monolitum\frontend\html\HtmlElement;

class BSFormSubmit extends FormSubmit
{
    use TraitBSButton;

    public function __construct(?Closure $builder)
    {
        parent::__construct(new HtmlElement("button"), $builder);
        $this->getElement()->setAttribute("type", "submit");
        $this->getElement()->addClass("btn");
        $this->getElement()->setRequireEndTag();
    }

    protected function onAfterBuild(): void
    {
        parent::onAfterBuild();
        $this->styleButton($this->getElement());
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


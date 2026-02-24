<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class FormControl extends HtmlElementNode
{

    public function __construct(HtmlElement $element, ?Closure $builder = null, private string $class = "form-control")
    {
        parent::__construct($element, $builder);
    }

    /**
     * @param $hint bool: "on", false: "off", string: "hint"
     * @return void
     */
    public function autocomplete(bool $hint = true): void
    {
        $element = $this->getElement();
        $element->setAttribute("autocomplete", $hint === true ? "on" : ($hint === false ? "off" : $hint));
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $element = $this->getElement();
        $element->setAttribute("name", $name);
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(?string $value): void
    {
        $element = $this->getElement();

        if($element->getTag() === "textarea"){
            $element->setContent($value);
        }else{
            $element->setAttribute("value", $value);
        }
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setDisabled(bool $value = true): void
    {
        $element = $this->getElement();
        $element->setAttribute("disabled", $value ? "disabled" : null);
    }

    public function convertToHidden(): void
    {
        $element = $this->getElement();
        $element->setTag("input");
        $element->setAttribute("type", "hidden");
    }

    protected function onBuild(): void
    {
        $this->addClass($this->class);
        parent::onBuild();
    }

}


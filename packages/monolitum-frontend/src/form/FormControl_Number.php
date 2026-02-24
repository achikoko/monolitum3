<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;

class FormControl_Number extends FormControl
{

    /**
     * @param callable|null $builder
     */
    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "number");
    }

    /**
     * @param int|null $value
     */
    public function min(?int $value): void
    {
        $element = $this->getElement();
        $element->setAttribute("min", $value);
    }

    /**
     * @param int|null $value
     */
    public function max(?int $value): void
    {
        $element = $this->getElement();
        $element->setAttribute("max", $value);
    }


    /**
     * @param mixed $value
     */
    public function step(int|float $value): void
    {
        $element = $this->getElement();
        $element->setAttribute("step", $value);
    }

}


<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;

class BSFlex extends BSDisplay
{

    private ?BSJustifyContent $justifyContent = null;

    private ?bool $row = null;
    private ?bool $reverse = null;

    /**
     * @param string $value
     */
    function __construct(string $value)
    {
        parent::__construct($value);
    }

    /**
     * @param bool $reverse
     * @return $this
     */
    public function row(bool $reverse = false): self
    {
        $this->row = true;
        $this->reverse = $reverse;
        return $this;
    }

    /**
     * @param bool $reverse
     * @return $this
     */
    public function col(bool $reverse = false): self
    {
        $this->row = false;
        $this->reverse = $reverse;
        return $this;
    }

    public function justifyContent(BSJustifyContent $justifyContent): self
    {
        $this->justifyContent = $justifyContent;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        parent::buildInto($component, $inverted);

        if($this->row !== null){
            if($this->row){
                if($this->reverse)
                    $component->addClass("flex-row-reverse");
                else
                    $component->addClass("flex-row");
            }else{

                if($this->reverse)
                    $component->addClass("flex-column-reverse");
                else
                    $component->addClass("flex-column");
            }
        }

        $this->justifyContent?->buildInto($component);

    }

}

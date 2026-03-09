<?php

namespace monolitum\bootstrap\component;

use monolitum\bootstrap\values\BSColor;
use monolitum\frontend\html\HtmlElement;

trait TraitBSButton
{

    private ?BSColor $color = null;
    private bool $outline = false;
    private bool $isLinkStyled = false;
    private ?bool $isLarge = null;

    public function color(BSColor $color, bool $outline = false): self
    {
        $this->color = $color;
        $this->outline = $outline;
        return $this;
    }

    public function colorLink(bool $colorLink = true): self
    {
        $this->isLinkStyled = $colorLink;
        return $this;
    }

    public function large(): self
    {
        $this->isLarge = true;
        return $this;
    }

    public function medium(): self
    {
        $this->isLarge = null;
        return $this;
    }

    public function small(): self
    {
        $this->isLarge = false;
        return $this;
    }

    protected function styleButton(HtmlElement $buttonElement): void
    {
        if($this->color != null){
            if($this->outline)
                $buttonElement->addClass("btn-outline-" . $this->color->getValue());
            else
                $buttonElement->addClass("btn-" . $this->color->getValue());
        }else{
            $buttonElement->addClass("btn-primary");
        }

        if($this->isLinkStyled){
            $buttonElement->addClass("btn-link");
        }

        if($this->isLarge !== null){
            if ($this->isLarge){
                $buttonElement->addClass("btn-lg");
            }else{
                $buttonElement->addClass("btn-sm");
            }
        }
    }

}

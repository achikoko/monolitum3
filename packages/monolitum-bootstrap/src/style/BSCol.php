<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSCol extends HtmlElementNodeExtension
{

    private BSColSpan|BSColSpanResponsive|int|null $span = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function of(): self
    {
        return new self();
    }

    public function span(int|BSColSpan|BSColSpanResponsive $span): self
    {
        $this->span = $span;
        return $this;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {

        if($this->span != null){
            if($this->span instanceof BSColSpanResponsive){
                $this->span->buildInto($component);
            }else if($this->span instanceof BSColSpan){
                $this->span->buildInto($component);
            }else{
                $component->addClass("col-" . $this->span);
            }
        }
        else
            $component->addClass("col");

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }
}

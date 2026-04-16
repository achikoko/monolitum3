<?php

namespace monolitum\bootstrap\tooltip;

use monolitum\bootstrap\BSPage;
use monolitum\frontend\html\HtmlBuilder;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\i18n\TS;

class BSTooltip extends HtmlElementNodeExtension
{

    private string|TS $content;

    public static function of(): BSTooltip
    {
        return new self();
    }

    public function content(string|TS|array $content): self
    {
        $this->content = is_array($content) ? TS::from($content) : $content;
        return $this;
    }

    public function apply(): void
    {
        BSPage::findSelf()->includePopperIfNot("tooltip");

        $element = $this->getElementComponent();

        $element->setAttribute("data-bs-toggle", "tooltip");

        if(TS::shouldBeRenderedAsRenderable($this->content)){
            $element->setAttribute("data-bs-html", "true");
            $rendered = TS::renderAuto($this->content);
            $html = new HtmlElement("p");
            $rendered->renderTo($html);
            $element->setAttribute("data-bs-title", (new HtmlBuilder())->renderContent($html, 0), false);
        }else{
            $element->setAttribute("data-bs-title", TS::unwrap($this->content));
        }

    }

}

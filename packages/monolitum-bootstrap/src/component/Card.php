<?php

namespace monolitum\bootstrap\component;

use Closure;
use monolitum\frontend\component\Div;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

class Card extends HtmlElementNode
{

    private $breakpoint;

    private ?Div $headerElement = null;

    private ?Div $footerElement = null;

    public function __construct(?Closure $builder)
    {
        parent::__construct(new HtmlElement("div"), $builder);
    }

    protected function onAfterBuild(): void
    {
        $this->addClass("card");

        if($this->headerElement !== null){
            $this->buildChildManually($this->headerElement);
        }

        if($this->footerElement !== null){
            $this->buildChildManually($this->footerElement);
        }

        parent::onAfterBuild();
    }

    protected function onExecute(): void
    {

        if($this->headerElement !== null){
            $this->executeChildManually($this->headerElement);
        }

        parent::onExecute();

        if($this->footerElement !== null){
            $this->executeChildManually($this->footerElement);
        }
    }

    public function render(): array|null|Renderable
    {
        if($this->headerElement !== null)
            Renderable_Node::renderRenderedTo($this->headerElement->render(), $this->getElement());
        Renderable_Node::renderRenderedTo(parent::renderChildren(), $this->getElement());
        if($this->footerElement !== null)
            Renderable_Node::renderRenderedTo($this->footerElement->render(), $this->getElement());
        return Rendered::of($this->getElement());
    }

    public function addHeader(string|HtmlElement|Renderable_Node ...$elements): void
    {
        if($this->headerElement === null){
            $this->headerElement = new Div();
            $this->headerElement->addClass("card-header");
        }
        foreach ($elements as $element){
            $this->headerElement->append($element);
        }
    }

    public function getHeaderElement(): ?Div
    {
        return $this->headerElement;
    }

    public function hasHeader(): bool
    {
        return $this->headerElement !== null;
    }

    public function addFooter(HtmlElement|Renderable_Node|string ...$elements): void
    {
        if($this->footerElement === null){
            $this->footerElement = new Div();
            $this->footerElement->addClass("card-footer");
        }
        foreach ($elements as $element){
            $this->footerElement->append($element);
        }
    }

    public function getFooterElement(): ?Div
    {
        return $this->footerElement;
    }

    public function hasFooter(): bool
    {
        return $this->footerElement !== null;
    }

}

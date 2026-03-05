<?php

namespace monolitum\frontend;

use Closure;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;

class Rendered implements Renderable {

    /**
     * @var HtmlElement|HtmlElementContent|null
     */
    private HtmlElementContent|HtmlElement|null $single = null;

    /**
     * @var array<HtmlElement|HtmlElementContent>
     */
    private ?array $multiple = null;

    /**
     * @return HtmlElement|HtmlElementContent|null
     */
    public function getSingle(): HtmlElement|HtmlElementContent|null
    {
        return $this->single;
    }

    /**
     * @param array|string|Renderable_Node|HtmlElement|HtmlElementContent|Rendered $element
     * @return Rendered
     */
    static function of(Rendered|Renderable_Node|array|string|HtmlElement|HtmlElementContent $element): Rendered
    {
        $r = new Rendered();
        if($element instanceof HtmlElement){
            $r->single = $element;
        }else if($element instanceof HtmlElementContent){
            $r->single = $element;
        }else if(is_string($element)){
            $r->single = new HtmlElementContent($element);
        }else if($element instanceof Renderable_Node){
            $r->append($element->render());
        }else if($element instanceof Rendered){
            $r->append($element);
        }else if(is_array($element)){
            foreach ($element as $element2){
                if($element2 instanceof HtmlElement){
                    $r->append($element2);
                }else if($element2 instanceof HtmlElementContent){
                    $r->append($element2);
                }else if(is_string($element)){
                    $r->append(new HtmlElementContent($element));
                }else if($element2 instanceof Renderable_Node){
                    $r->append($element2->render());
                }else if($element2 instanceof Rendered){
                    $r->append($element2);
                }
            }
        }
        return $r;
    }

    /**
     * @return Rendered
     */
    static function ofEmpty(): Rendered
    {
        return new Rendered();
    }

    /**
     * @param Rendered|HtmlElement|HtmlElementContent|null $renderedComponent
     */
    public function append(Rendered|HtmlElement|HtmlElementContent|null $renderedComponent): void
    {
        if($renderedComponent === null)
            return;

        $element = null;
        if(
            $renderedComponent instanceof HtmlElement ||
            $renderedComponent instanceof HtmlElementContent ||
            ($element = $renderedComponent->single) != null
        ){
            if($element == null)
                $element = $renderedComponent;

            if($this->multiple == null){
                if($this->single == null){
                    $this->single = $element;
                }else{
                    $this->multiple = [$this->single, $element];
                    $this->single = null;
                }
            }else{
                $this->multiple[] = $element;
            }
        }else if($renderedComponent->multiple != null){
            if($this->multiple == null) {
                if($this->single != null){
                    $this->multiple = [$this->single];
                    $this->single = null;
                }else{
                    $this->multiple = [];
                }
            }
            foreach ($renderedComponent->multiple as $single) {
                $this->multiple[] = $single;
            }
        }
    }

    /**
     * @param HtmlElement $element
     */
    function renderTo(HtmlElement $element): void
    {
        if($this->single != null){
            if($this->single instanceof HtmlElement)
                $element->addChildElement($this->single);
            else
                $element->addContent($this->single->content);
        } else if($this->multiple != null){
            foreach ($this->multiple as $single){

                if($single instanceof HtmlElement)
                    $element->addChildElement($single);
                else
                    $element->addContent($single->content);

            }
        }
    }

    function buildChildrenTo(Closure $nodeBuilderAndInserter): void
    {
        if($this->single != null){
            $nodeBuilderAndInserter($this->single);
        }else if ($this->multiple != null){
            foreach ($this->multiple as $single){
                $nodeBuilderAndInserter($single);
            }
        }
    }

    function onNotReceived()
    {
        throw new DevPanic("Rendered was not received.");
    }
}

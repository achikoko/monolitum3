<?php

namespace monolitum\bootstrap\container;

use monolitum\backend\globals\Request_NewId;
use monolitum\core\ExplicitAcceptChildNode;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\Div;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\frontend\Renderable_Node;

class Accordion extends HtmlElementNode
{

    private bool $canOpenMultiple = false;

    /** @var array<Accordion_Item> */
    private array $items = [];

    private array $builtHeaders = [];
    private array $builtBodies = [];


    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("div"), $builder);
        $this->addClass("accordion");
    }

    /**
     * @param bool $canOpenMultiple
     */
    public function setCanOpenMultiple(bool $canOpenMultiple = true): Accordion
    {
        $this->canOpenMultiple = $canOpenMultiple;
        return $this;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof Accordion_Item){
            $this->items[] = $object;
            return true;
        }else if($object instanceof Renderable_Node && !($object instanceof ExplicitAcceptChildNode)){
            throw new DevPanic("Accordion only accepts Accordion_Item children.");
        }else if($object instanceof HtmlElementNodeExtension){
            return parent::doAcceptChild($object);
        }
        // We return false to tell the acceptor to forward a not recognized object to the parent.
        // (If we called the parent, it would accept it mistakenly)
        return false;
    }

    protected function onAfterBuild(): void
    {

        $id = Request_NewId::pushAndGet();
        $this->setId($id);

        foreach ($this->items as $item){

            $idItem = Request_NewId::pushAndGet();

            $divItem = new HtmlElementNode(new HtmlElement("div"));
            $divItem->addClass("accordion-item");

            $h2 = new HtmlElementNode(new HtmlElement("h2"));
            $h2->addClass("accordion-header");

            $button = new HtmlElementNode(new HtmlElement("button"));
            $button->addClass("accordion-button");
            if($item->isCollapsed())
                $button->addClass("collapsed");
            $button->setAttribute("data-bs-toggle", "collapse");
            $button->setAttribute("data-bs-target", "#" . $idItem);
            $button->append($item->getHeader()); // Already built

            $h2->append($button);
            $divItem->append($h2);

            $divCollapse = new Div();
            $divCollapse->setId($idItem);
            $divCollapse->addClass("accordion-collapse", "collapse");
            if(!$item->isCollapsed())
                $divCollapse->addClass("show");

            if(!$this->canOpenMultiple)
                $divCollapse->setAttribute("data-bs-parent", "#" . $id);

            $divBody = new Div();
            $divBody->addClass("accordion-body");
            $divBody->append($item->getBody());

            $divCollapse->append($divBody);
            $divItem->append($divCollapse);

            $this->append($divItem);

        }

        parent::onAfterBuild();
    }

}

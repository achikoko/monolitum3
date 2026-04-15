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
use monolitum\i18n\TS;
use function monolitum\core\m;

/**
 * https://getbootstrap.com/docs/5.3/components/navs-tabs/#tabs
 */
class Tabs extends HtmlElementNode
{

    private bool $pills = false;

    /** @var array<Tabs_Item> */
    private array $items = [];

    private array $builtHeaders = [];
    private array $builtBodies = [];


    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("div"), $builder);
    }

    public function asPills(bool $pills = true): Tabs
    {
        $this->pills = $pills;
        return $this;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof Tabs_Item){
            $this->items[] = $object;
            return true;
        }else if($object instanceof Renderable_Node && !($object instanceof ExplicitAcceptChildNode)){
            throw new DevPanic("Tabs only accepts Tabs_Item children.");
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

        $ul = new HtmlElement("ul");
        $ul->addClass("nav", $this->pills ? "nav-pills" : "nav-tabs");
        $ul->setAttribute("role", "tablist");

        $ids = [];

        foreach ($this->items as $item) {

            $idItem = Request_NewId::pushAndGet();

            $li = new HtmlElement("li");
            $li->addClass("nav-item");
            $li->setAttribute("role", "presentation");

            $button = new HtmlElement("button");
            $button->setId("$idItem-tab");
            $button->addClass("nav-link");
            $button->setAttribute("type", "button");
            $button->setAttribute("data-bs-toggle", $this->pills ? "pill" : "tab");
            $button->setAttribute("data-bs-target", "#$idItem");
            if($item->isOpen()){
                $button->addClass("active");
            }
            $button->addContent(TS::unwrap($item->getTitle()));

            $li->addChildElement($button);
            $ul->addChildElement($li);
            $ids[] = $idItem;

        }

        $this->append($ul);

        $this->append(new Div(function (Div $it) use ($ids) {
            $it->addClass("tab-content");

            $i = 0;
            foreach ($this->items as $item) {
                $itemId = $ids[$i++];

                $it->append(new Div(function (Div $it) use ($item, $itemId) {

                    $it->setId("$itemId");
                    $it->setAttribute("role", "tabpanel");
                    $it->addClass("tab-pane");
                    $it->addClass("fade");

                    if($item->isOpen()){
                        $it->addClass("show", "active");
                    }

                    M($item->getBody());

                }));

            }

        }));

        parent::onAfterBuild();
    }

}

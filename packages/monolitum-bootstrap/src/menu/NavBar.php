<?php

namespace monolitum\bootstrap\menu;

use monolitum\backend\globals\Request_NewId;
use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\bootstrap\component\BSButton;
use monolitum\bootstrap\style\BSMargin;
use monolitum\bootstrap\style\BSVerticalAlign;
use monolitum\bootstrap\values\BSConstants;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\Component;
use monolitum\frontend\component\A;
use monolitum\frontend\component\Div;
use monolitum\frontend\component\Img;
use monolitum\frontend\component\Li;
use monolitum\frontend\component\Span;
use monolitum\frontend\component\Ul;
use monolitum\frontend\css\CSSSize;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable_Node;

class NavBar extends HtmlElementNode implements Menu_Item_Holder
{

    private string $expandBreakpoint = BSConstants::BREAKPOINT_LG;

    private Path|Link|null $brandLink = null;

    private Path|HtmlElementNode|null $brandIcon = null;

    private string|HtmlElementNode|null $brandTitle = null;

    private bool $themeDark = false;

    /**
     * @var array
     */
    private array $leftItems = [];

    private string|HtmlElementNode|null|array $rightComponent = null;

    private ?Ul $leftItemsUl = null;
    private ?Ul $rightItemsUl = null;

    public function __construct($builder)
    {
        parent::__construct(new HtmlElement("div"), $builder);
        $this->addClass("navbar");
    }

    public function setExpandBreakpoint(string $expandBreakpoint): self
    {
        $this->expandBreakpoint = $expandBreakpoint;
        return $this;
    }

    public function setDark(): self
    {
        $this->themeDark = true;
        return $this;
    }

    public function brandLink($brandLink): self
    {
        $this->brandLink = $brandLink;
        return $this;
    }

    public function brandTitle(HtmlElementNode|string $brandTitle): self
    {
        if($brandTitle instanceof HtmlElementNode)
            $this->buildAndAppendChild($brandTitle);
        $this->brandTitle = $brandTitle;
        return $this;
    }

    public function brandIcon(HtmlElementNode|Path|null $brandIcon): self
    {
        if($brandIcon instanceof HtmlElementNode)
            $this->buildAndAppendChild($brandIcon);
        $this->brandIcon = $brandIcon;
        return $this;
    }

    public function addLeft(Menu_Item_Dropdown|Menu_Item $leftItem): self
    {
        $this->leftItems[] = $leftItem;
        return $this;
    }

    public function addRight(Menu_Item|string|Renderable_Node $rightItem): self
    {
        if($this->rightComponent === null){
            $this->rightComponent = $rightItem;
        }else if(is_array($this->rightComponent)) {
            $this->rightComponent[] = $rightItem;
        }else {
            $c = $this->rightComponent;
            $this->rightComponent = [$c, $rightItem];
        }
        return $this;
    }

//    /**
//     * @param Nav_Item|mixed $leftItem
//     * @return Li
//     */
//    public function createMenuItemLi($leftItem, $submenu=false, $right=false)
//    {
//        if($leftItem == null){
//            // Divider
//            $li2 = new Li();
//
//            $a2 = new Hr();
//            $a2->addClass("dropdown-divider");
//
//            return $li2;
//        }
//
//        //<li class="nav-item">
//        $li = new Li();
//
//        if(!$submenu){
//            $li->addClass("nav-item");
//        } else{
//            $this->assureSubmenuCodeAdded();
//            $a->addClass("dropdown-item");
//        }
//
//
//        if(!($leftItem instanceof Nav_Item)){
//            $li->append($leftItem);
//            return $li;
//        }
//
//        if ($leftItem instanceof Nav_Item_Dropdown){
//            $li->addClass("dropdown");
//
//            if($submenu){
//
//                if($right)
//                    $li->addClass("dropleft");
//                else
//                    $li->addClass("dropright");
//
//                $li->addClass("dropdown-submenu");
//            }
//
//        }
//
//        //<a class="nav-link active" aria-current="page" href="#">Home</a>
//        $a = new A();
//        if(!$submenu){
//            $a->addClass("nav-link");
//        }
//
//        if ($leftItem instanceof Nav_Item_Dropdown) {
//            $a->addClass("dropdown-toggle");
//            $a->setAttribute("data-bs-toggle", "dropdown");
//        }
//
//        $this->setupItem($leftItem, $a);
//
//        $li->append($a);
//
//        if ($leftItem instanceof Nav_Item_Dropdown) {
//
//            $ul2 = new Ul();
//            $ul2->addClass("dropdown-menu");
//            if(!$submenu){
//                if($right)
//                    $ul2->addClass("dropdown-menu-right");
//            }
//
//            foreach ($leftItem->getItems() as $dropdownItem) {
//                $li2 = $this->createMenuItemLi($dropdownItem, true, $right);
//                $ul2->append($li2);
//            }
//
//            $li->append($ul2);
//
//        }
//        return $li;
//    }
//
//    /**
//     * @param Nav_Item $leftItem
//     * @param A $a
//     * @return void
//     */
//    public function setupItem($leftItem, ElementComponent $a)
//    {
//        if ($leftItem->isActive()) {
//            $a->addClass("active");
//            $a->setAttribute("aria-current", "page");
//        }
//        if ($leftItem->isDisabled()) {
//            $a->addClass("disabled");
//        } else {
//            $link = $leftItem->getLink();
//            $a->setHref($link);
//        }
//        $a->setContent($leftItem->getText());
//    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof Renderable_Node){
            throw new DevPanic("NavBar does not accept renderable nodes out of specific methods.");
        }
        return parent::doAcceptChild($object);
    }

    public function openToLeft(): bool
    {
        if($this->rightItemsUl !== null){
            // Setting up right items
            return true;
        }else{
            return false;
        }
    }

    public function isSubmenu(): bool
    {
        return false;
    }

    public function isNav(): bool
    {
        return true;
    }

    protected function onAfterBuild(): void
    {

        $this->addClass("navbar-expand-" . $this->expandBreakpoint);

        if($this->themeDark)
            $this->addClass("navbar-dark", "bg-dark");
        else
            $this->addClass("bg-light");

        {

            $fluid = new Div();
            $fluid->addClass("container-fluid");

            {

                $brand = new A();
                $brand->addClass("navbar-brand");

                if($this->brandLink != null){
                    $brand->setHref($this->brandLink);
                }

                if($this->brandIcon !== null){
                    if($this->brandIcon instanceof Renderable_Node){
                        $brand->append($this->brandIcon);
                    } else if($this->brandIcon instanceof Path){
                        $img = new Img();
                        $img->setSource($this->brandIcon);
                        $img->style()->height(CSSSize::px(35));
                        $brand->append($img);
                    }
                }

                if($this->brandTitle !== null){
                    if(is_string($this->brandTitle)){
                        $component = Span::from($this->brandTitle);
                        $component->receive(BSVerticalAlign::middle());
                    }
                    else
                        $component = $this->brandTitle;

                    if($this->brandIcon !== null)
                        $component->receive(BSMargin::left(2));

                    $brand->append($component);
                }

                $fluid->append($brand);

            }

            $hasItems = !empty($this->leftItems) || $this->rightComponent !== null;

            if($hasItems){

                /*
                 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                */

                $id = Request_NewId::pushAndGet();

                $button = new BSButton();
                $button->addClass("navbar-toggler");
                $button->setAttribute("data-bs-toggle", "collapse");
                $button->setAttribute("data-bs-target", "#" . $id);
                $button->setAttribute("aria-controls", "#" . $id);
                {

                    $span = new Span();
                    $span->addClass("navbar-toggler-icon");

                    $button->append($span);

                }
                $fluid->append($button);

                //<div class="collapse navbar-collapse" id="navbarText">
                $divCollapse = new Div();
                $divCollapse->addClass("collapse navbar-collapse");
                $divCollapse->setId($id);

                if(!empty($this->leftItems)){

                    //<ul class="navbar-nav me-auto mb-5 mb-lg-0">
                    $this->leftItemsUl = new Ul();
                    $this->leftItemsUl->addClass("navbar-nav");
                    //$ul->marginLeft("auto");
                    //$ul->marginBottom(5);
                    //$ul->marginBottom(5, $this->expandBreakpoint);

                    foreach ($this->leftItems as $item) {
                        //$li = $this->createMenuItemLi($item, false, false);
                        if(is_string($item)){
                            //<span class="navbar-text">
                            $span = new Span();
                            $span->addClass("navbar-text");
                            $span->setContent($this->rightComponent);
                            $this->leftItemsUl->append($span);

                        }else{

                            $li = new Li();
                            $li->addClass("nav-item");
                            $li->append($item);
                            $this->leftItemsUl->append($item);
                        }

                    }

                    $divCollapse->append($this->leftItemsUl);

                }

                if($this->rightComponent !== null) {
                    if (is_array($this->rightComponent)) {
                        if (!empty($this->rightComponent)) {

                            //<ul class="navbar-nav me-auto mb-5 mb-lg-0">
                            $this->rightItemsUl = new Ul();
                            $this->rightItemsUl->addClass("navbar-nav", "ms-auto");
                            {
                                foreach ($this->rightComponent as $item) {
                                    //$li = $this->createMenuItemLi($item, false, true);
                                    if (is_string($item)) {
                                        //<span class="navbar-text">
                                        $span = new Span();
                                        $span->addClass("navbar-text");
                                        $span->setContent($this->rightComponent);
                                        $this->rightItemsUl->append($span);

                                    } else {
                                        $this->rightItemsUl->append($item);
                                    }

                                }

                            }
                            $divCollapse->append($this->rightItemsUl);

                        }
                    } else if (is_string($this->rightComponent)) {
                        //<span class="navbar-text">
                        $span = new Span();
                        $span->addClass("navbar-text");
                        $span->setContent($this->rightComponent);
                        $divCollapse->append($span);

                    } else {
                        $this->rightComponent->receive(BSMargin::left());
                        $divCollapse->append($this->rightComponent);
                    }
                }

                $fluid->append($divCollapse);

            }

            $this->append($fluid);

        }

        parent::onAfterBuild();
    }

}

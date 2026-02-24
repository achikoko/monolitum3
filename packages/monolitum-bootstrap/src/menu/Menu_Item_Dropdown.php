<?php

namespace monolitum\bootstrap\menu;

use monolitum\bootstrap\BSPage;
use monolitum\core\Find;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\A;
use monolitum\frontend\component\Ul;
use monolitum\frontend\Renderable_Node;
use function monolitum\core\m;

class Menu_Item_Dropdown extends Menu_Item implements Menu_Item_Holder
{

    /**
     * @var array<Menu_Item|Menu_Separator|Menu_Item_Dropdown>
     */
    private array $items = [];

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof Menu_Item || $object instanceof Menu_Separator){
            $this->items[] = $object;
            return true;
        }else if($object instanceof Renderable_Node && Monolitum::getInstance()->getCurrentBuildingNode() !== $this){
            throw new DevPanic("Menu_Item_Dropdown only accepts Menu_Item or Menu_Separator children.");
        }
        // We return false to tell the acceptor to forward a not recognized object to the parent.
        // (If we called the parent, it would accept it mistakenly)
        return false;
    }

    /**
     * @return array<Menu_Item|Menu_Separator>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function openToLeft(): bool
    {
        return $this->getMenuItemHolder()->openToLeft();
    }

    public function isSubmenu(): bool
    {
        return true;
    }

    public function isNav(): bool
    {
        return false;
    }

    protected function onBuild(): void
    {
        BSPage::findSelf()->includePopperIfNot();
        parent::onBuild();
    }

    protected function buildElement(): void
    {

        if($this->getMenuItemHolder()->isNav()){
            $this->addClass("nav-item");
            $this->addClass("dropdown");
        }else{
            if($this->isSubmenu()){
                if($this->getMenuItemHolder()->openToLeft()){
                    $this->addClass("dropend");
                }else{
                    $this->addClass("dropstart");
                }
            }
        }

        // Test submenu

        $this->append(new A(function (A $it) {

            if($this->getMenuItemHolder()->isNav()){
                $it->addClass("nav-link");
                $it->setAttribute("data-bs-auto-close", "outside");
            }else{
                //$this->assureSubmenuCodeAdded();
                $it->addClass("dropdown-item");
            }

            if($this->active){
                $it->addClass("active");
            }
            if($this->disabled){
                $it->addClass("disabled");
            }else{
                // Make dropdown menu
                $it->addClass("dropdown-toggle");
                $it->setAttribute("tabindex", "0");
                $it->setAttribute("data-bs-toggle", "dropdown");

//                if(!$isNav){
//                    $it->setAttribute("data-submenu", "");
//                }

            }
            $it->setContent($this->text);

        }));

        if(!$this->disabled){
           $this->append(new Ul(function (Ul $it){
                $it->addClass("dropdown-menu");

                foreach ($this->items as $item){
                    $it->append($item);
                }

            }));

        }

    }

}

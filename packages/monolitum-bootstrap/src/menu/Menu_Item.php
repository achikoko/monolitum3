<?php

namespace monolitum\bootstrap\menu;

use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\core\Find;
use monolitum\frontend\component\A;
use monolitum\frontend\component\Li;

class Menu_Item extends Li
{

    protected string $text;

    private Path|Link|null $link = null;

    protected bool $active = false;

    protected bool $disabled = false;

    private ?Menu_Item_Holder $menuItemHolder = null;

    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @return Menu_Item_Holder|null
     */
    public function getMenuItemHolder(): ?Menu_Item_Holder
    {
        if($this->menuItemHolder === null)
            $this->menuItemHolder = Find::pushAndGetFrom(Menu_Item_Holder::class, $this->getParent());

        return $this->menuItemHolder;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function text(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getLink(): Link|Path|null
    {
        return $this->link;
    }

    public function link(Link|Path $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function active(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        $this->buildElement();
        parent::onAfterBuild();
    }

    protected function buildElement(): void
    {

        if($this->getMenuItemHolder()->isNav()){
            $this->addClass("nav-item");
        }

        $this->append(A::of(function (A $it) {
            // TODO this can be wrong if there is portals or references
            if($this->getMenuItemHolder()->isNav()){
                $it->addClass("nav-link");
            }else {
                // In a dropdown
                $it->addClass("dropdown-item");
            }

            if($this->active){
                $it->addClass("active");
            }
            if($this->disabled){
                $it->addClass("disabled");
            }else{
                $it->setHref($this->link);
            }
            $it->setContent($this->text);

        }));

    }

    /**
     * @param string $content
     * @return Menu_Item
     */
    public static function of(string $content): Menu_Item
    {
        $item = new Menu_Item();
        $item->text($content);
        return $item;
    }

}

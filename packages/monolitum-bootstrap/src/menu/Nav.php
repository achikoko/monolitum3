<?php

namespace monolitum\bootstrap\menu;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class Nav extends HtmlElementNode implements Menu_Item_Holder
{

    private ?string $type = null;

    private bool $fill = false;

    private bool $vertical = false;

    public function __construct($builder)
    {
        parent::__construct(new HtmlElement("ul"), $builder);
        $this->addClass("nav");
    }

    public function isNav(): bool
    {
        return true;
    }

    public function openToLeft(): bool
    {
        return false;
    }

    public function isSubmenu(): bool
    {
        return false;
    }

    public function pills(): self
    {
        $this->type = "pills";
        return $this;
    }

    public function tabs(): self
    {
        $this->type = "tabs";
        return $this;
    }

    public function fill(): self
    {
        $this->fill = true;
        return $this;
    }

    public function vertical(): self
    {
        $this->vertical = true;
        return $this;
    }

    protected function onAfterBuild(): void
    {

        if($this->type)
            $this->addClass("nav-" . $this->type);

        if($this->vertical)
            $this->addClass("flex-column");

        if($this->fill)
            $this->addClass("nav-fill");

        parent::onAfterBuild();
    }

}

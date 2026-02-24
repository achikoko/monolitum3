<?php

namespace monolitum\fontawesome;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

class FA_Icon extends HtmlElementNode
{

    const SOLID = "solid";

    private string $collection = FA_Icon::SOLID;

    private string $icon;

    /**
     * @param callable $builder
     */
    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement('i'), $builder);
    }

    public function setCollectionIcon(string $collection, string $icon): void
    {
        $this->collection = $collection;
        $this->icon = $icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    protected function onAfterBuild(): void
    {
        $this->addClass("fa-" . $this->collection, "fa-" . $this->icon);
        parent::onAfterBuild();
    }

    public static function fromIcon(string $icon, ?Closure $builder = null): FA_Icon
    {
        $i = new FA_Icon($builder);
        $i->setIcon($icon);
        return $i;
    }

    public static function fromCollectionIcon(string $collection, string $icon, ?Closure $builder = null): FA_Icon
    {
        $i = new FA_Icon($builder);
        $i->setCollectionIcon($collection, $icon);
        return $i;
    }


}

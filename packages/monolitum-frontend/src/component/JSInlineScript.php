<?php

namespace monolitum\frontend\component;

use monolitum\backend\params\Path;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

class JSInlineScript extends Renderable_Node {

    private array $scripts = [];

    /**
     * @param Path $path
     * @param $builder
     */
    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param string $script
     * @return $this
     */
    public function addScript(string $script){
        $this->scripts[] = $script;
        return $this;
    }

    public function render(): Renderable|array|null
    {
        $link = new HtmlElement("script");
        $link->setContent((new HtmlElementContent(implode("", $this->scripts)))->setRaw());

        return Rendered::of($link);
    }

}

<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable_Node;

class B extends AbstractTextNode
{

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("b"), $builder);
    }

    /**
     * @param string|Renderable_Node $content
     * @return B
     */
    public static function from(string|Renderable_Node $content): B
    {
        $fc = new B();
        $fc->append($content);
        return $fc;

    }

    public static function of(?Closure $builder = null): B
    {
        return new B($builder);
    }

}
